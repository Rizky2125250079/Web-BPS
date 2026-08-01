<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class BpsApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $defaultDomain;

    public function __construct()
    {
        // Ambil dari config/services.php (dengan fallback ke env)
        $rawBaseUrl = config('services.bps.base', env('BPS_API_BASE', 'https://webapi.bps.go.id/v1/'));

        // Membersihkan trailing slash agar pembentukan URL konsisten
        $this->baseUrl = rtrim($rawBaseUrl, '/');
        $this->apiKey = config('services.bps.key', env('BPS_API_KEY'));
        $this->defaultDomain = config('services.bps.domain', env('BPS_DOMAIN_DEFAULT', '0000'));
    }

    /**
     * Mengambil daftar publikasi menggunakan endpoint /list
     */
    public function getPublications($domain = null, $page = 1, $keyword = '')
    {
        $domain = $domain ?? $this->defaultDomain;

        // Hasil URL: https://webapi.bps.go.id/v1/api/list/model/publication/lang/ind/domain/{domain}/key/{key}/page/{page}
        $url = "{$this->baseUrl}/api/list/model/publication/lang/ind/domain/{$domain}/key/{$this->apiKey}/page/{$page}";

        if (!empty($keyword)) {
            $url .= "/keyword/" . urlencode($keyword);
        }

        $response = Http::get($url);

        // Kembalikan payload API BPS apa adanya (status, data-availability, data[0]=meta, data[1]=list),
        // karena PublicationController::welcome() membaca $apiData['status'] dan $apiData['data'][0]['pages']
        // untuk membangun pagination. Jika di sini kita sudah "membongkar" ke data[1] saja,
        // controller tidak akan pernah menemukan total halaman -> pagination selalu stuck di halaman 1.
        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Mengambil beberapa halaman endpoint /list model=publication SECARA
     * PARALEL (bersamaan) memakai HTTP connection pool Laravel, bukan
     * satu-satu berurutan.
     *
     * Kenapa perlu: sebelumnya paginasi dilakukan satu request per halaman,
     * menunggu response selesai baru lanjut ke halaman berikutnya. Kalau
     * hasilnya tersebar di puluhan halaman, waktu tunggu ikut menumpuk
     * (mis. 40 halaman x 0.7 detik = 28 detik) dan gampang melebihi batas
     * max_execution_time PHP -> fatal error "Maximum execution time of 30
     * seconds exceeded". Dengan pool, semua halaman diminta bersamaan dan
     * total waktu tunggu kira-kira sama dengan request PALING LAMBAT saja,
     * bukan jumlah semuanya. Tiap request dibatasi timeout 10 detik supaya
     * satu halaman yang lambat/macet tidak ikut menyeret seluruh pool.
     *
     * @param  array<int,int>  $pages  nomor halaman yang mau diambil (mis. [2,3,4,...])
     * @return array<int,array>  peta [nomor_halaman => payload JSON halaman itu]
     */
    private function fetchPagesInParallel(string $domain, string $keyword, array $pages): array
    {
        if (empty($pages)) {
            return [];
        }

        $buildUrl = function (int $page) use ($domain, $keyword) {
            $url = "{$this->baseUrl}/api/list/model/publication/lang/ind/domain/{$domain}/key/{$this->apiKey}/page/{$page}";

            if (!empty($keyword)) {
                $url .= "/keyword/" . urlencode($keyword);
            }

            return $url;
        };

        $responses = Http::pool(function ($pool) use ($pages, $buildUrl) {
            foreach ($pages as $page) {
                $pool->as((string) $page)->timeout(10)->get($buildUrl($page));
            }
        });

        $results = [];
        foreach ($pages as $page) {
            $response = $responses[(string) $page] ?? null;

            // Http::pool() mengembalikan objek exception (bukan Response) untuk
            // request yang gagal/timeout -> lewati saja, jangan sampai fatal error.
            if ($response instanceof Response && $response->successful()) {
                $results[$page] = $response->json();
            }
        }

        return $results;
    }

    /**
     * Mengambil SEMUA hasil pencarian untuk sebuah keyword (bukan cuma satu
     * halaman) dengan memaginasi endpoint /list model=publication.
     *
     * Diperlukan karena publikasi yang judulnya paling cocok dengan keyword
     * bisa saja berada di halaman manapun dari hasil API BPS (urutan asal API
     * berdasarkan tanggal rilis, bukan relevansi judul) — kalau cuma menyortir
     * satu halaman saja, publikasi paling relevan bisa "terkubur" di halaman
     * lain dan tidak pernah kelihatan naik ke atas.
     *
     * Halaman pertama diambil dulu (untuk tahu total halaman), lalu sisa
     * halaman (2..N) diambil PARALEL lewat fetchPagesInParallel() supaya
     * tidak kena "Maximum execution time exceeded" saat hasilnya tersebar
     * di banyak halaman.
     *
     * Hasil di-cache per (domain, keyword) selama 30 menit supaya pencarian
     * dengan kata kunci yang sama tidak menghajar API BPS berkali-kali.
     *
     * @return array<int,array> gabungan seluruh item publikasi dari semua halaman
     */
    public function searchAllPublications(string $keyword, $domain = null, int $maxPages = 20): array
    {
        $domain = $domain ?? $this->defaultDomain;
        $cacheKey = 'bps_pub_search_' . $domain . '_' . md5(mb_strtolower(trim($keyword)));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($domain, $keyword, $maxPages) {
            $first = $this->getPublications($domain, 1, $keyword);

            $items = [];
            if (isset($first['data'][1]) && is_array($first['data'][1])) {
                $items = $first['data'][1];
            }

            $totalPages = isset($first['data'][0]['pages']) ? (int) $first['data'][0]['pages'] : 1;
            $lastPage   = min($totalPages, $maxPages);

            if ($lastPage >= 2) {
                foreach ($this->fetchPagesInParallel($domain, $keyword, range(2, $lastPage)) as $json) {
                    if (isset($json['data'][1]) && is_array($json['data'][1])) {
                        $items = array_merge($items, $json['data'][1]);
                    }
                }
            }

            return $items;
        });
    }

    /**
     * Mengambil seluruh tanggal rilis (rl_date) publikasi dengan memaginasi
     * endpoint /list model=publication (endpoint yang sama dan sudah terbukti
     * berfungsi di getPublications()).
     *
     * CATATAN: endpoint list model=publication milik WebAPI BPS TIDAK mendukung
     * parameter "year" sebagai filter (berbeda dengan model=data). Percobaan
     * sebelumnya (getPublicationCountByYear via /year/{tahun}) selalu
     * mengembalikan total 0 sehingga grafik "Jumlah Publikasi per Tahun" di
     * halaman utama selalu kosong. Solusinya: ambil semua data lewat paginasi
     * biasa lalu hitung tahunnya sendiri dari rl_date.
     *
     * Sama seperti searchAllPublications(): halaman pertama diambil dulu untuk
     * tahu total halaman, sisanya diambil PARALEL supaya tidak kena "Maximum
     * execution time exceeded" — method ini jalan di SETIAP load halaman utama
     * (bukan cuma saat search), jadi risikonya sama besar bahkan tanpa keyword.
     *
     * Hasil di-cache (bukan per tahun lagi, tapi satu cache untuk seluruh
     * daftar tanggal) supaya tidak memaginasi ulang API BPS setiap kali
     * halaman utama dibuka.
     *
     * @return array<int,string> daftar rl_date mentah, mis. ['2024-05-01', ...]
     */
    protected function getAllPublicationDates($domain = null, int $maxPages = 20): array
    {
        $domain = $domain ?? $this->defaultDomain;
        $cacheKey = "bps_pub_all_dates_{$domain}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($domain, $maxPages) {
            $dates = [];

            $collectDates = function (?array $json) use (&$dates) {
                if (isset($json['data'][1]) && is_array($json['data'][1])) {
                    foreach ($json['data'][1] as $item) {
                        if (!empty($item['rl_date'])) {
                            $dates[] = $item['rl_date'];
                        }
                    }
                }
            };

            $first = $this->getPublications($domain, 1, '');
            $collectDates($first);

            $totalPages = isset($first['data'][0]['pages']) ? (int) $first['data'][0]['pages'] : 1;
            $lastPage   = min($totalPages, $maxPages);

            if ($lastPage >= 2) {
                foreach ($this->fetchPagesInParallel($domain, '', range(2, $lastPage)) as $json) {
                    $collectDates($json);
                }
            }

            return $dates;
        });
    }

    /**
     * Mengambil jumlah publikasi untuk rentang tahun (startYear..endYear),
     * dipakai untuk grafik "jumlah publikasi per tahun" di halaman utama.
     * Menghitung dari rl_date hasil getAllPublicationDates(), bukan dari
     * parameter "year" pada API (lihat catatan di getAllPublicationDates()).
     *
     * @return array<int,int> [2020 => 12, 2021 => 15, ...]
     */
    public function getPublicationCountsByYearRange(int $startYear, int $endYear, $domain = null): array
    {
        $counts = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $counts[$year] = 0;
        }

        foreach ($this->getAllPublicationDates($domain) as $rlDate) {
            $year = (int) substr($rlDate, 0, 4);

            if (isset($counts[$year])) {
                $counts[$year]++;
            }
        }

        return $counts;
    }

    /**
     * Mengambil detail publikasi menggunakan endpoint /view
     */
    public function getPublicationDetail($pubId, $domain = null)
    {
        $domain = $domain ?? $this->defaultDomain;

        // Hasil URL: https://webapi.bps.go.id/v1/api/view/model/publication/lang/ind/domain/{domain}/id/{pubId}/key/{key}/
        $url = "{$this->baseUrl}/api/view/model/publication/lang/ind/domain/{$domain}/id/{$pubId}/key/{$this->apiKey}/";

        $response = Http::get($url);

        if ($response->successful() && isset($response->json()['data'])) {
            return $response->json()['data'];
        }

        return null;
    }
}

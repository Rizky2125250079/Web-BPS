<?php

namespace App\Http\Controllers;

use App\Services\BpsApiService;
use App\Models\Publication;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    protected BpsApiService $bpsApi;

    public function __construct(BpsApiService $bpsApi)
    {
        $this->bpsApi = $bpsApi;
    }

    /**
     * Jumlah item per halaman untuk hasil pencarian (paginasi lokal, lihat
     * catatan pada blok "if (!empty($keyword))" di welcome()).
     */
    private const SEARCH_PER_PAGE = 10;

    public function welcome(Request $request)
    {
        // UI Anda dimulai dari halaman 1
        $page    = (int) $request->input('page', 1);
        $keyword = $request->input('search', '');
        $domain  = $request->input('domain', env('BPS_DOMAIN_DEFAULT', '0000'));

        // Nilai Default
        $currentPage     = $page; // Gunakan request page agar konsisten dengan URL UI
        $totalPages      = 1;
        $apiPublications = [];

        if (!empty($keyword)) {
            // PENTING: saat ada keyword, JANGAN ambil satu halaman API saja
            // (getPublications) lalu di-sort di situ. Publikasi yang paling
            // relevan (mis. judul diawali keyword) bisa saja berada di
            // halaman manapun dari hasil API BPS, karena API tidak
            // mengurutkan berdasarkan relevansi judul (biasanya urut tanggal
            // rilis). Kalau cuma menyortir satu halaman, publikasi paling
            // relevan bisa "terkubur" di halaman lain dan tidak pernah naik
            // ke atas -> itulah sebabnya judul yang kurang relevan bisa
            // tampil lebih dulu daripada yang lebih relevan.
            //
            // searchAllPublications() mengambil SEMUA halaman untuk keyword
            // ini dan meng-cache hasilnya 30 menit (lihat BpsApiService),
            // jadi request berikutnya dengan keyword sama (pindah halaman,
            // atau user lain mencari kata yang sama) tidak menghajar API BPS
            // lagi -> tidak menambah beban kinerja web.
            $allResults = $this->bpsApi->searchAllPublications($keyword, $domain);

            // Urutkan SELURUH hasil (lintas halaman) berdasarkan relevansi judul.
            $allResults = $this->sortByTitleRelevance($allResults, $keyword);

            $totalPages  = max(1, (int) ceil(count($allResults) / self::SEARCH_PER_PAGE));
            $currentPage = min(max(1, $page), $totalPages);

            // Paginasi lokal dari hasil yang sudah terurut relevansinya.
            $apiPublications = array_slice(
                $allResults,
                ($currentPage - 1) * self::SEARCH_PER_PAGE,
                self::SEARCH_PER_PAGE
            );
        } else {
            // Tanpa keyword: perilaku browsing biasa, tidak berubah sama
            // sekali (satu halaman langsung dari API, tanpa sorting relevansi
            // karena tidak relevan tanpa keyword) -> tidak ada beban tambahan
            // di jalur ini.
            $apiData = $this->bpsApi->getPublications($domain, $page, $keyword);

            // Parsing struktur response API BPS.
            // PENTING: jangan cek $apiData['status'] === 'OK' sebagai syarat, karena
            // saat keyword pencarian tidak ditemukan API BPS membalas status "Error"
            // padahal strukturnya (data[0]=meta, data[1]=list publikasi) tetap sama,
            // cuma data[1] isinya array kosong. Kalau kita fallback ke "$apiData
            // mentah" di sini, seluruh payload API (status, data-availability, data)
            // akan ikut ter-render sebagai kartu publikasi palsu ("Judul Tidak
            // Tersedia" -> link ke publikasi yang tidak ada -> 404).
            if (isset($apiData['data'][0]['pages'])) {
                $totalPages = max(1, (int) $apiData['data'][0]['pages']);
            }

            if (isset($apiData['data'][1]) && is_array($apiData['data'][1])) {
                $apiPublications = $apiData['data'][1];
            }
        }

        $localPublications = Publication::latest()->take(5)->get();

        // Grafik jumlah publikasi per tahun (default 8 tahun terakhir), ditampilkan langsung di beranda
        $chartEndYear   = now()->year;
        $chartStartYear = $chartEndYear - 7;
        $yearlyCounts   = $this->bpsApi->getPublicationCountsByYearRange($chartStartYear, $chartEndYear, $domain);

        return view('welcome', compact(
            'apiPublications',
            'localPublications',
            'keyword',
            'currentPage',
            'totalPages',
            'yearlyCounts',
            'chartStartYear',
            'chartEndYear'
        ));
    }

    public function show($id)
    {
        $detail = $this->bpsApi->getPublicationDetail($id);
        return view('publications.show', compact('detail'));
    }

    /**
     * Menampilkan grafik jumlah publikasi per tahun.
     * Default menampilkan 8 tahun terakhir, bisa diubah lewat ?from=&to=
     */
    public function statistikTahunan(Request $request)
    {
        $domain   = $request->input('domain', env('BPS_DOMAIN_DEFAULT', '0000'));
        $endYear  = (int) $request->input('to', now()->year);
        $startYear = (int) $request->input('from', $endYear - 7);

        $yearlyCounts = $this->bpsApi->getPublicationCountsByYearRange($startYear, $endYear, $domain);

        return view('publications.statistik', compact('yearlyCounts', 'startYear', 'endYear', 'domain'));
    }

    /**
     * Mengurutkan daftar publikasi (seluruh hasil pencarian, lintas halaman
     * API) agar yang judulnya paling cocok dengan keyword pencarian tampil
     * paling atas. Urutan asal antar item dengan skor relevansi yang sama
     * tetap dipertahankan (stable sort, dijamin sejak PHP 8.0).
     *
     * @param  array<int,array>  $publications
     */
    private function sortByTitleRelevance(array $publications, string $keyword): array
    {
        usort($publications, function ($a, $b) use ($keyword) {
            return $this->titleRelevanceScore($a['title'] ?? '', $keyword)
                <=> $this->titleRelevanceScore($b['title'] ?? '', $keyword);
        });

        return $publications;
    }

    /**
     * Skor relevansi judul terhadap keyword. Semakin kecil skor, semakin
     * relevan (dipakai sebagai kunci sort ascending):
     *   0 = judul sama persis dengan keyword
     *   1 = judul diawali keyword
     *   2 = keyword muncul sebagai kata utuh di judul
     *   3 = keyword muncul sebagai bagian dari kata di judul
     *   4 = keyword tidak ditemukan di judul (kemungkinan cocok di field lain)
     */
    private function titleRelevanceScore(string $title, string $keyword): int
    {
        $title   = mb_strtolower(trim($title));
        $keyword = mb_strtolower(trim($keyword));

        if ($keyword === '') {
            return 4;
        }

        if ($title === $keyword) {
            return 0;
        }

        if (str_starts_with($title, $keyword)) {
            return 1;
        }

        if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/u', $title) === 1) {
            return 2;
        }

        if (str_contains($title, $keyword)) {
            return 3;
        }

        return 4;
    }
}

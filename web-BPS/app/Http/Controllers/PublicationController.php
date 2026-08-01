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
     * catatan pada blok "if (!empty($keyword))" di buildListing()).
     */
    private const SEARCH_PER_PAGE = 10;

    public function welcome(Request $request)
    {
        // PENTING: jangan andalkan default argumen kedua pada $request->input('x', default).
        // Laravel secara bawaan memasang middleware ConvertEmptyStringsToNull yang mengubah
        // SEMUA input string kosong ('') menjadi null SEBELUM sampai ke controller. Akibatnya,
        // kalau field year/search dikirim kosong (mis. hidden input yang belum diisi), key-nya
        // tetap ADA di request tapi nilainya null -> default '' pada input() tidak akan pernah
        // dipakai (default hanya berlaku kalau key-nya sama sekali tidak ada), dan null tsb lolos
        // ke buildListing() yang mensyaratkan tipe string -> TypeError. Makanya di sini semua
        // nilai dibungkus dengan ?? supaya null (baik karena key tidak ada, maupun karena
        // dikonversi middleware) selalu jatuh ke default yang benar.
        $domain = $request->input('domain') ?? env('BPS_DOMAIN_DEFAULT', '0000');

        // Satu search bar dipakai bersama untuk mencari DUA bagian sekaligus
        // (Publikasi & Press Release). Filter tahun tetap terpisah per
        // bagian: year untuk Publikasi, year_brs untuk Press Release.
        $keyword = (string) ($request->input('search') ?? '');

        // ==== Bagian Publikasi ====
        // Query params: search (dipakai bersama), year, page
        $page = (int) ($request->input('page') ?? 1);
        $year = (string) ($request->input('year') ?? '');

        [$apiPublications, $currentPage, $totalPages] = $this->buildListing(
            $keyword,
            $year,
            $page,
            fn ($kw, $yr) => $this->bpsApi->searchAllPublications($kw, $domain, 20, $yr),
            fn ($pg, $yr) => $this->bpsApi->getPublications($domain, $pg, $keyword, $yr)
        );

        // ==== Bagian Press Release (Berita Resmi Statistik) ====
        // Query params: search (dipakai bersama), year_brs, page_brs
        $pageBrs = (int) ($request->input('page_brs') ?? 1);
        $yearBrs = (string) ($request->input('year_brs') ?? '');

        [$apiPressReleases, $currentPageBrs, $totalPagesBrs] = $this->buildListing(
            $keyword,
            $yearBrs,
            $pageBrs,
            fn ($kw, $yr) => $this->bpsApi->searchAllPressReleases($kw, $domain, 20, $yr),
            fn ($pg, $yr) => $this->bpsApi->getPressReleases($domain, $pg, $keyword, $yr)
        );

        $localPublications = Publication::latest()->take(5)->get();

        // Daftar tahun untuk dropdown filter (10 tahun terakhir s.d. tahun berjalan).
        // Dipakai bersama oleh kedua bagian (Publikasi & Press Release).
        $availableYears = range(now()->year, now()->year - 9);

        return view('welcome', compact(
            'apiPublications',
            'localPublications',
            'keyword',
            'year',
            'availableYears',
            'currentPage',
            'totalPages',
            'apiPressReleases',
            'yearBrs',
            'currentPageBrs',
            'totalPagesBrs'
        ));
    }

    public function show($id)
    {
        $detail = $this->bpsApi->getPublicationDetail($id);
        return view('publications.show', compact('detail'));
    }

    public function showPressRelease($id)
    {
        $detail = $this->bpsApi->getPressReleaseDetail($id);
        return view('pressreleases.show', compact('detail'));
    }

    /**
     * Logika bersama untuk membangun daftar item (Publikasi atau Press
     * Release) beserta paginasinya:
     *
     * - Jika ada keyword: ambil SEMUA halaman lewat $searchAll (lintas
     *   halaman API), urutkan berdasarkan relevansi judul, baru dipaginasi
     *   secara lokal (lihat catatan lengkap di sortByTitleRelevance()).
     * - Jika tidak ada keyword: browsing biasa, satu halaman langsung dari
     *   API lewat $getList, hanya ditambah filter tahun jika dipilih user.
     *
     * @param  callable(string,string):array<int,array>  $searchAll  fn(keyword, year) -> semua item
     * @param  callable(int,string):array  $getList  fn(page, year) -> payload mentah API BPS
     * @return array{0: array<int,array>, 1: int, 2: int}  [items, currentPage, totalPages]
     */
    private function buildListing(string $keyword, string $year, int $page, callable $searchAll, callable $getList): array
    {
        $currentPage = $page;
        $totalPages  = 1;
        $items       = [];

        if (!empty($keyword)) {
            // PENTING: saat ada keyword, JANGAN ambil satu halaman API saja
            // lalu di-sort di situ. Item yang judulnya paling cocok dengan
            // keyword bisa saja berada di halaman manapun dari hasil API BPS
            // (urutan asal API berdasarkan tanggal rilis, bukan relevansi
            // judul) — kalau cuma menyortir satu halaman, item paling
            // relevan bisa "terkubur" di halaman lain dan tidak pernah naik
            // ke atas.
            //
            // $searchAll mengambil SEMUA halaman untuk keyword (dan tahun,
            // jika dipilih) ini dan hasilnya di-cache 30 menit di
            // BpsApiService, jadi request berikutnya dengan keyword dan
            // tahun yang sama tidak menghajar API BPS lagi.
            $allResults = $searchAll($keyword, $year);

            // Urutkan SELURUH hasil (lintas halaman) berdasarkan relevansi judul.
            $allResults = $this->sortByTitleRelevance($allResults, $keyword);

            $totalPages  = max(1, (int) ceil(count($allResults) / self::SEARCH_PER_PAGE));
            $currentPage = min(max(1, $page), $totalPages);

            // Paginasi lokal dari hasil yang sudah terurut relevansinya.
            $items = array_slice(
                $allResults,
                ($currentPage - 1) * self::SEARCH_PER_PAGE,
                self::SEARCH_PER_PAGE
            );
        } else {
            // Tanpa keyword: perilaku browsing biasa (satu halaman langsung
            // dari API, tanpa sorting relevansi karena tidak relevan tanpa
            // keyword), hanya ditambah filter tahun jika dipilih user.
            $apiData = $getList($page, $year);

            // Parsing struktur response API BPS.
            // PENTING: jangan cek $apiData['status'] === 'OK' sebagai syarat, karena
            // saat keyword pencarian tidak ditemukan API BPS membalas status "Error"
            // padahal strukturnya (data[0]=meta, data[1]=list) tetap sama, cuma
            // data[1] isinya array kosong. Kalau kita fallback ke "$apiData mentah"
            // di sini, seluruh payload API (status, data-availability, data) akan
            // ikut ter-render sebagai kartu palsu ("Judul Tidak Tersedia" -> link ke
            // item yang tidak ada -> 404).
            if (isset($apiData['data'][0]['pages'])) {
                $totalPages = max(1, (int) $apiData['data'][0]['pages']);
            }

            if (isset($apiData['data'][1]) && is_array($apiData['data'][1])) {
                $items = $apiData['data'][1];
            }
        }

        return [$items, $currentPage, $totalPages];
    }

    /**
     * Mengurutkan daftar item (seluruh hasil pencarian, lintas halaman API)
     * agar yang judulnya paling cocok dengan keyword pencarian tampil paling
     * atas. Urutan asal antar item dengan skor relevansi yang sama tetap
     * dipertahankan (stable sort, dijamin sejak PHP 8.0). Dipakai bersama
     * oleh Publikasi maupun Press Release karena keduanya sama-sama punya
     * field 'title'.
     *
     * @param  array<int,array>  $items
     */
    private function sortByTitleRelevance(array $items, string $keyword): array
    {
        usort($items, function ($a, $b) use ($keyword) {
            return $this->titleRelevanceScore($a['title'] ?? '', $keyword)
                <=> $this->titleRelevanceScore($b['title'] ?? '', $keyword);
        });

        return $items;
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

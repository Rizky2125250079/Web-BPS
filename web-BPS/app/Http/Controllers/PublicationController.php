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

 public function welcome(Request $request)
    {
        // UI Anda dimulai dari halaman 1
        $page    = (int) $request->input('page', 1);
        $keyword = $request->input('search', '');
        $domain  = $request->input('domain', env('BPS_DOMAIN_DEFAULT', '0000'));

        // Ambil data dari API BPS via Service
        $apiData = $this->bpsApi->getPublications($domain, $page, $keyword);

        // Nilai Default
        $currentPage     = $page; // Gunakan request page agar konsisten dengan URL UI
        $totalPages      = 1;
        $apiPublications = [];

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

        // Prioritaskan publikasi yang judulnya cocok dengan keyword pencarian.
        // API BPS sendiri tidak menjamin urutan berdasarkan relevansi judul
        // (biasanya urut tanggal rilis), jadi kita urutkan ulang di sini.
        if (!empty($keyword)) {
            $apiPublications = $this->sortByTitleRelevance($apiPublications, $keyword);
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
     * Mengurutkan daftar publikasi (dalam satu halaman hasil API) agar yang
     * judulnya paling cocok dengan keyword pencarian tampil paling atas.
     * Urutan asal antar item dengan skor relevansi yang sama tetap
     * dipertahankan (stable sort, dijamin sejak PHP 8.0).
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

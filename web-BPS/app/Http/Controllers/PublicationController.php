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

    // Menampilkan welcome.blade.php sebagai Halaman Utama / Index Publikasi
    public function welcome(Request $request)
    {
        $page    = (int) $request->input('page', 1);
        $keyword = $request->input('search', '');
        $domain  = $request->input('domain', env('BPS_DOMAIN_DEFAULT', '0000'));

        // Ambil data dari API BPS via Service
        $apiData = $this->bpsApi->getPublications($domain, $page, $keyword);

        // Ekstrak meta pagination & daftar publikasi
        // Jika BpsApiService mengembalikan respon raw BPS API:
        // - Indeks [0] berisi meta: 'page', 'pages', 'total', dll.
        // - Indeks [1] berisi array publikasi
        $currentPage     = (int) ($apiData['data'][0]['page'] ?? $page);
        $totalPages      = (int) ($apiData['data'][0]['pages'] ?? 1);
        $apiPublications = $apiData['data'][1] ?? $apiData; // Fallback jika service sudah mengembalikan data[1] langsung

        // Ambil data lokal buatan admin
        $localPublications = Publication::latest()->take(5)->get();

        return view('welcome', compact(
            'apiPublications',
            'localPublications',
            'keyword',
            'currentPage',
            'totalPages'
        ));
    }

    public function show($id)
    {
        $detail = $this->bpsApi->getPublicationDetail($id);
        return view('publications.show', compact('detail'));
    }
}

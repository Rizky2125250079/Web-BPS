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

        // Parsing berdasarkan struktur sukses dari API BPS
        if (isset($apiData['status']) && $apiData['status'] === 'OK' && isset($apiData['data'])) {

            // Index [0] berisi Meta Data (page, pages, per_page, dll)
            if (isset($apiData['data'][0]['pages'])) {
                $totalPages = (int) $apiData['data'][0]['pages'];
            }

            // Index [1] berisi Array Data Publikasi
            if (isset($apiData['data'][1]) && is_array($apiData['data'][1])) {
                $apiPublications = $apiData['data'][1];
            }

        } else {
            // Fallback jika API gagal atau mengembalikan format yang berbeda
            $apiPublications = is_array($apiData) ? $apiData : [];
        }

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

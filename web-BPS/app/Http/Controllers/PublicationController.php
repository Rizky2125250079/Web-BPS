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
        $page = $request->input('page', 1);
        $keyword = $request->input('search', '');
        $domain = $request->input('domain', env('BPS_DOMAIN_DEFAULT'));

        // Ambil data dari API BPS
        $apiPublications = $this->bpsApi->getPublications($domain, $page, $keyword);

        // Ambil data lokal buatan admin
        $localPublications = Publication::latest()->take(5)->get();

        return view('welcome', compact('apiPublications', 'localPublications', 'keyword'));
    }

    public function show($id)
    {
        $detail = $this->bpsApi->getPublicationDetail($id);
        return view('publications.show', compact('detail'));
    }
}

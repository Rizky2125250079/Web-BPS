<?php

namespace App\Http\Controllers;

use App\Services\BpsApiService;
use Illuminate\Http\Request;

class DatasetController extends Controller
{
    protected BpsApiService $bpsApi;

    public function __construct(BpsApiService $bpsApi)
    {
        $this->bpsApi = $bpsApi;
    }

    public function index(Request $request)
    {
        $domain = $request->input('domain') ?? env('BPS_DOMAIN_DEFAULT', '0000');
        $keyword = (string) ($request->input('keyword') ?? '');
        $page = max(1, (int) ($request->input('page') ?? 1));

        $response = $this->bpsApi->getStaticTables($domain, $page, $keyword);
        $info = $response['data'][0] ?? [];
        $tables = $response['data'][1] ?? [];
        $totalPages = max(1, (int) ($info['pages'] ?? 1));

        // Kalau user minta halaman di luar batas (mis. ?page=999), tarik ulang
        // ke halaman terakhir yang valid supaya tombol next/prev tidak nyangkut.
        if ($page > $totalPages) {
            $page = $totalPages;
            $response = $this->bpsApi->getStaticTables($domain, $page, $keyword);
            $info = $response['data'][0] ?? [];
            $tables = $response['data'][1] ?? [];
        }

        return view('dataset.index', [
            'tables' => $tables,
            'keyword' => $keyword,
            'currentPage' => (int) ($info['page'] ?? $page),
            'totalPages' => $totalPages,
            'totalDataset' => (int) ($info['total'] ?? count($tables)),
        ]);
    }

    public function show($id)
    {
        $detail = $this->bpsApi->getStaticTableDetail($id);

        return view('dataset.show', compact('detail'));
    }
}
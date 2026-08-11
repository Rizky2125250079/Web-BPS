<?php

namespace App\Http\Controllers;

use App\Services\BpsApiService;
use App\Models\Announcement;
use Illuminate\Http\Request;

class PublicationController extends Controller
{
    protected BpsApiService $bpsApi;

    public function __construct(BpsApiService $bpsApi)
    {
        $this->bpsApi = $bpsApi;
    }

    private const SEARCH_PER_PAGE = 15;

    public function welcome(Request $request)
    {

        $domain = $request->input('domain') ?? env('BPS_DOMAIN_DEFAULT', '0000');

        $keyword = (string) ($request->input('search') ?? '');

        $page = (int) ($request->input('page') ?? 1);
        $year = (string) ($request->input('year') ?? '');

        [$apiPublications, $currentPage, $totalPages] = $this->buildListing(
            $keyword,
            $year,
            $page,
            fn ($kw, $yr) => $this->bpsApi->searchAllPublications($kw, $domain, 20, $yr),
            fn ($pg, $yr) => $this->bpsApi->getPublications($domain, $pg, $keyword, $yr)
        );


        $pageBrs = (int) ($request->input('page_brs') ?? 1);
        $yearBrs = (string) ($request->input('year_brs') ?? '');

        [$apiPressReleases, $currentPageBrs, $totalPagesBrs] = $this->buildListing(
            $keyword,
            $yearBrs,
            $pageBrs,
            fn ($kw, $yr) => $this->bpsApi->searchAllPressReleases($kw, $domain, 20, $yr),
            fn ($pg, $yr) => $this->bpsApi->getPressReleases($domain, $pg, $keyword, $yr)
        );


        $announcements = Announcement::with('user')->latest()->get();


        $availableYears = range(now()->year, now()->year - 9);

        return view('welcome', compact(
            'apiPublications',
            'announcements',
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


    private function buildListing(string $keyword, string $year, int $page, callable $searchAll, callable $getList): array
    {
        $currentPage = $page;
        $totalPages  = 1;
        $items       = [];

        if (!empty($keyword)) {

            $allResults = $searchAll($keyword, $year);
            $allResults = $this->sortByTitleRelevance($allResults, $keyword);

            $totalPages  = max(1, (int) ceil(count($allResults) / self::SEARCH_PER_PAGE));
            $currentPage = min(max(1, $page), $totalPages);


            $items = array_slice(
                $allResults,
                ($currentPage - 1) * self::SEARCH_PER_PAGE,
                self::SEARCH_PER_PAGE
            );
        } else {

            $apiData = $getList($page, $year);

            if (isset($apiData['data'][0]['pages'])) {
                $totalPages = max(1, (int) $apiData['data'][0]['pages']);
            }

            if (isset($apiData['data'][1]) && is_array($apiData['data'][1])) {
                $items = $apiData['data'][1];
            }
        }

        return [$items, $currentPage, $totalPages];
    }


    private function sortByTitleRelevance(array $items, string $keyword): array
    {
        usort($items, function ($a, $b) use ($keyword) {
            return $this->titleRelevanceScore($a['title'] ?? '', $keyword)
                <=> $this->titleRelevanceScore($b['title'] ?? '', $keyword);
        });

        return $items;
    }

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

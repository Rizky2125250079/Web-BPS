<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

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
            $url .= "/keyword/{$keyword}";
        }

        $response = Http::get($url);

        if ($response->successful() && isset($response->json()['data'][1])) {
            return $response->json()['data'][1];
        }

        return [];
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

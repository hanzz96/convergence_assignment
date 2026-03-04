<?php

namespace App\Libraries\Api\Strapi;

use App\Libraries\Api\BaseApi;
use App\Libraries\StrapiQueryBuilder;
use GuzzleHttp\RequestOptions;

class StrapiApi extends BaseApi
{
    private $guzzleClient;

    public function __construct()
    {
        parent::__construct(new BaseConfig(config('services.strapi.url'), env('STRAPI_API_KEY')));
        $this->guzzleClient = $this->createGuzzleClient();
    }

    public function getContents(StrapiQueryBuilder $strapiQuery)
    {
        $url = 'articles';
        $response = $this->guzzleClient->request(
            'GET',
            $url,
            [
                RequestOptions::HEADERS => $this->composeHeaders($this->apiConfig->apiKey),
                RequestOptions::QUERY => $strapiQuery->toArray()
            ]
        );
        return $response;
    }

    public function getContentById(int $id, ?StrapiQueryBuilder $strapiQuery = null)
    {
        $url = "articles/{$id}";
        $response = $this->guzzleClient->get($url, [
            RequestOptions::HEADERS => $this->composeHeaders($this->apiConfig->apiKey),
            RequestOptions::QUERY => $strapiQuery->toArray() ?? []
        ]);

        return $response;
    }

    private function composeHeaders(string $accessToken): array
    {
        return [
            'Authorization' => "Bearer $accessToken",
        ];
    }
}

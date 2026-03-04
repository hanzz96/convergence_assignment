<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Libraries\Api\Strapi\StrapiApi;
use App\Libraries\StrapiQueryBuilder;
use Exception;

class StrapiServices
{
    private StrapiApi $api;

    public function __construct(StrapiApi $api)
    {
        $this->api = $api;
    }

    public function getContents(int $page = 1, int $perPage = 10)
    {
        try {
            $cacheKey = "strapi_contents_page_{$page}";

            $cached = Redis::get($cacheKey);

            if ($cached) {
                return json_decode($cached, true);
            }

            $strapiQueryBuilder = new StrapiQueryBuilder();

            $response = $this->api->getContents($strapiQueryBuilder);

            $httpCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->__toString());
            // dd($response,'response');
            // dd($body, 'body');
            if ($httpCode == 200) {
                Redis::setex($cacheKey, 60, json_encode($body));
                return $body;
            } else if ($httpCode >= 400 && $httpCode <= 600) {
                // if (\property_exists($body, 'ErrorCode')) {
                //     $this->errorCheck($body->ErrorCode);
                // }

                // if (\property_exists($body, 'error_code')) {
                //     $this->errorCheck($body->error_code);
                // }

                throw new Exception('api_strapi_error');
            } else {
                throw new Exception('api_strapi_error_critical');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getContentById(int $id)
    {
        try {

            $cacheKey = "strapi_content_{$id}";

            $cached = Redis::get($cacheKey);

            if ($cached) {
                return json_decode($cached, true);
            }

            $response = $this->api->getContentById($id);

            $httpCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->__toString());

            if ($httpCode == 200) {
                Redis::setex($cacheKey, 60, json_encode($body));
                return $body;
            } else if ($httpCode >= 400 && $httpCode <= 600) {
                throw new Exception('api_strapi_error');
            } else {
                throw new Exception('api_strapi_error_critical');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

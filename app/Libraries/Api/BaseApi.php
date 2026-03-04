<?php

namespace App\Libraries\Api;

use App\Libraries\Api\Strapi\BaseConfig;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class BaseApi
{
    protected BaseConfig $apiConfig;
    private $defaultOptions = [
        RequestOptions::TIMEOUT => 10,
        RequestOptions::HTTP_ERRORS => false
    ];

    private $defaultHeaders = [
        'accept' => 'application/json',
        'content-type' => 'application/json',
    ];

    protected $options = [];

    protected $headers = [];

    public function __construct(BaseConfig $apiConfig)
    {
        $this->apiConfig = $apiConfig;
    }

    public function createGuzzleClient(array $configs = []): Client
    {
        $illuminateRequest = request();

        $message = $illuminateRequest->method() . ' ' . $illuminateRequest->url();

        /**
         * Merge headers
         */
        $headers = array_merge($this->defaultHeaders ?? [], $this->headers ?? []);

        if (isset($configs[RequestOptions::HEADERS])) {
            $headers = array_merge($headers, $configs[RequestOptions::HEADERS]);
            unset($configs[RequestOptions::HEADERS]);
        }

        /**
         * Merge options
         */
        $options = array_merge($this->defaultOptions ?? [], $this->options ?? [], $configs);

        /**
         * Handler stack
         */
        $stack = HandlerStack::create(new CurlHandler());

        $payloadRequest = [];

        /**
         * Request logger middleware
         */
        $stack->push(Middleware::mapRequest(
            function (RequestInterface $request) use ($message, &$payloadRequest) {

                $body = (string) $request->getBody();

                $payloadRequest = [
                    'requestMethod' => $request->getMethod(),
                    'requestUri' => (string) $request->getUri(),
                    'requestHeader' => $request->getHeaders(),
                    'requestBody' => $body ? json_decode($body, true) ?? $body : null
                ];

                Log::info($message, $payloadRequest);

                return $request;
            }
        ));

        /**
         * Response logger middleware
         */
        $stack->push(Middleware::mapResponse(
            function (ResponseInterface $response) use ($message, &$payloadRequest) {

                $body = (string) $response->getBody();
                $decoded = json_decode($body, true);

                $responseData = [
                    'responseStatusCode' => $response->getStatusCode(),
                    'responseHeader' => $response->getHeaders(),
                    'responseBody' => $decoded ?? $body
                ];

                if ($response->getStatusCode() === 200) {

                    Log::info($message, $responseData);
                } else {

                    Log::critical($message, array_merge($payloadRequest, $responseData));
                }

                return $response;
            }
        ));

        /**
         * Final client config
         */
        $config = array_merge([
            'base_uri' => $this->apiConfig->apiUrl,
            RequestOptions::HEADERS => $headers,
            'handler' => $stack,
            'timeout' => 10
        ], $options);

        return new Client($config);
    }

    /**
     * 
     * Get API Config
     * 
     * @return BaseConfig
     */
    public function getApiConfig(): BaseConfig
    {
        return $this->apiConfig;
    }
}

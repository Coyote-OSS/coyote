<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;

readonly class HttpClient {
    private GuzzleHttp\Client $http;

    public function __construct(string $baseUrl) {
        $this->http = new GuzzleHttp\Client(['base_uri' => $baseUrl, 'http_errors' => false]);
    }

    public function get(string $path): HttpResponse {
        return $this->httpResponse($this->http->get($path));
    }

    public function post(string $path, ?array $body = null): HttpResponse {
        return $this->httpResponse($this->httpPost($path, $body));
    }

    private function httpPost(string $path, ?array $body): ResponseInterface {
        return $this->http->post($path, $body === null
            ? []
            : ['json' => $body]);
    }

    private function httpResponse(ResponseInterface $response): HttpResponse {
        return new HttpResponse($response->getStatusCode(), $response->getBody());
    }
}

<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

readonly class HttpResponse {
    public function __construct(
        public int     $status,
        private string $body,
    ) {}

    public function json(): array {
        return \json_decode($this->body, true);
    }
}

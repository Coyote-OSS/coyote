<?php
namespace Tests\Legacy\Integration\BaseFixture\Server;

class Url
{
    private string $scheme;
    private string $hostname;
    private ?string $path;
    private ?string $query;

    public function __construct(string $hostname, string $url)
    {
        $parts = $this->components($hostname,$url);
        $this->scheme = $parts['scheme'];
        $this->hostname = $parts['host'];
        $this->path = $parts['path'];
        $this->query = $parts['query'];
    }

    private function components(string $defaultHostname, string $url): array
    {
        return \parse_url($url) + [
                'scheme' => 'https',
                'host'   => $defaultHostname,
                'path'   => null,
                'query'  => null,
            ];
    }

    public function __toString(): string
    {
        $base = "$this->scheme://$this->hostname";
        if ($this->path) {
            $base .= $this->path;
        }
        if ($this->query !== null) {
            $base .= '?' . $this->query;
        }
        return $base;
    }
}

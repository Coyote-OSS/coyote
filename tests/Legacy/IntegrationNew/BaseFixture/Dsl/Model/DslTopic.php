<?php
namespace Tests\Legacy\IntegrationNew\BaseFixture\Dsl\Model;

use Illuminate\Support\Str;

readonly class DslTopic
{
    public ?int $id;

    public function __construct(
        public string $title,
        ?string       $url,
    )
    {
        if ($url) {
            $this->id = $this->parseTopicId($url);
        } else {
            $this->id = null;
        }
    }

    private function parseTopicId(string $url): string
    {
        $urlPath = \parse_url($url, \PHP_URL_PATH);
        $urlPathTopicPart = $this->removePrefix($urlPath, '/Forum/Newbie/');
        return Str::before($urlPathTopicPart, '-');
    }

    private function removePrefix(string $path, string $needle): string
    {
        if (!\str_starts_with($path, $needle)) {
            throw new \Exception("Failed to remove string prefix: $needle");
        }
        return \subStr($path, \strLen('/Forum/Newbie/'));
    }
}

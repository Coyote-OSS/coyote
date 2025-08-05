<?php
namespace Coyote\Domain\View\Filter;

use Coyote\Domain\Administrator\UserMaterial\List\Store\SearchFilter;
use Coyote\Domain\Administrator\UserMaterial\List\Store\SearchFilterType;

readonly class SearchFilterFormat {
    public function __construct(private string $filter) {}

    public function toSearchFilter(): SearchFilter {
        $array = ['type' => SearchFilterType::Post,];
        foreach ($this->filterTokens() as $token) {
            [$key, $value] = $this->parseToken($token);
            $array[$key] = $value;
            if ($key === 'report') {
                $array['reported'] = true;
            }
            if ([$key, $value] === ['reported', false]) {
                $array['report'] = null;
            }
        }
        return new SearchFilter(
            $array['type'],
            $array['deleted'] ?? null,
            $array['reported'] ?? null,
            $array['report'] ?? null,
            $array['author'] ?? null);
    }

    private function parseToken(string $token): ?array {
        return $this->parseInteger($token, 'author') ??
            $this->parseBoolean($token, 'reported') ??
            $this->choice($token, 'report', [
                'open'   => true,
                'closed' => false,
            ]) ??
            $this->parseBoolean($token, 'deleted') ??
            $this->choice($token, 'type', [
                'post'      => SearchFilterType::Post,
                'comment'   => SearchFilterType::PostComment,
                'microblog' => SearchFilterType::Blog,
            ]);
    }

    private function parseInteger(string $format, string $key): ?array {
        $parts = \explode(':', $format);
        if (count($parts) === 1) {
            return null;
        }
        [$k, $value] = $parts;
        if ($k === $key && \cType_digit($value)) {
            return [$key, (int)$value];
        }
        return null;
    }

    private function parseBoolean(string $token, string $key): ?array {
        if ($token === "is:$key") {
            return [$key, true];
        }
        if ($token === "not:$key") {
            return [$key, false];
        }
        return null;
    }

    private function choice(string $format, string $key, array $values): ?array {
        foreach ($values as $value => $returnValue) {
            if ($format === "$key:$value") {
                return [$key, $returnValue];
            }
        }
        return null;
    }

    private function filterTokens(): array {
        return \explode(' ', $this->filter);
    }
}

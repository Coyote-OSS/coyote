<?php
namespace Coyote\Domain\View\Filter;

use Coyote\Domain\Administrator\UserMaterial\List\Store\SearchFilter;
use Coyote\Domain\Administrator\UserMaterial\List\Store\SearchFilterType;

readonly class SearchFilterFormat {
    public function __construct(private string $filter) {}

    public function toSearchFilter(): SearchFilter {
        $ar = $this->toArray();
        return new SearchFilter(
            $ar['type'] ?? SearchFilterType::Post,
            $ar['deleted'] ?? null,
            $ar['reported'] ?? null,
            $ar['open'] ?? null,
            $ar['author'] ?? null);
    }

    private function toArray(): array {
        $array = [];
        foreach (\explode(' ', $this->filter) as $format) {
            $this->integer($array, $format, 'author');
            $this->boolean($array, $format, 'reported');
            $this->boolean($array, $format, 'open');
            $this->boolean($array, $format, 'deleted');
            $this->choice($array, $format, 'type', [
                'post'      => SearchFilterType::Post,
                'comment'   => SearchFilterType::PostComment,
                'microblog' => SearchFilterType::Blog,
            ]);
        }
        return $array;
    }

    private function integer(array &$array, string $format, string $key): void {
        $parts = \explode(':', $format);
        if (count($parts) === 1) {
            return;
        }
        [$k, $value] = $parts;
        if ($k === $key) {
            if (\cType_digit($value)) {
                $array[$key] = (int)$value;
            }
        }
    }

    private function boolean(array &$array, string $format, string $key): void {
        if ($format === 'is:' . $key) {
            $array[$key] = true;
        }
        if ($format === 'not:' . $key) {
            $array[$key] = false;
        }
    }

    private function choice(array &$array, string $format, string $key, array $values): void {
        foreach ($values as $value => $returnValue) {
            if ($format === "$key:$value") {
                $array[$key] = $returnValue;
            }
        }
    }
}

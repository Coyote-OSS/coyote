<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\Store;

readonly class SearchFilter {
    public function __construct(
        public SearchFilterType $type,
        public ?bool            $deleted,
        public ?bool            $reported,
        public ?bool            $reportOpen,
        public ?int             $authorId,
    ) {}

    public function toString(): string {
        $values = ["type:{$this->type()}"];
        if ($this->reported !== null) {
            $values[] = $this->reported ? 'is:reported' : 'not:reported';
        }
        if ($this->reportOpen !== null) {
            $values[] = $this->reportOpen ? 'report:open' : 'report:closed';
        }
        if ($this->deleted !== null) {
            $values[] = $this->deleted ? 'is:deleted' : 'not:deleted';
        }
        if ($this->authorId !== null) {
            $values[] = "author:$this->authorId";
        }
        return \implode(' ', $values);
    }

    private function type(): string {
        return match ($this->type) {
            SearchFilterType::Post        => 'post',
            SearchFilterType::PostComment => 'comment',
            SearchFilterType::Blog        => 'microblog',
        };
    }
}

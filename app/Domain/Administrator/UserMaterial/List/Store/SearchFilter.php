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
}

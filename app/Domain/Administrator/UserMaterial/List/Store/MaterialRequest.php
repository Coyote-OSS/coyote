<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\Store;

class MaterialRequest {
    public function __construct(
        public int          $page,
        public int          $pageSize,
        public SearchFilter $filter,
    ) {}
}

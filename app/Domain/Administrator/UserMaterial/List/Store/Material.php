<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\Store;

use Carbon\Carbon;

readonly class Material {
    public function __construct(
        public int     $id,
        public string  $type,
        public Carbon  $createdAt,
        public ?Carbon $deletedAt,
        public ?Carbon $parentDeletedAt,
        public ?int    $parentId,
        public string  $authorUsername,
        public ?string $authorImageUrl,
        public string  $contentMarkdown,
        public bool    $reported,
        public bool    $reportOpen,
        public string  $href,
    ) {}
}

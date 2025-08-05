<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\View;

use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\List\Store\Material;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialResult;

readonly class MaterialList {
    public function __construct(
        private MarkdownRender $render,
        private Time           $time,
        private MaterialResult $materials,
        private AvatarCdn      $cdn,
    ) {}

    public function total(): int {
        return $this->materials->total;
    }

    public function items(): array {
        return \array_map($this->viewObject(...), $this->materials->materials);
    }

    private function viewObject(Material $material): MaterialItem {
        return new MaterialItem(
            $this->type($material),
            $this->time->date($material->createdAt),
            $this->time->dateOptional($material->parentDeletedAt ?? $material->deletedAt),
            $material->authorUsername,
            $this->cdn->avatar($material->authorImageUrl),
            $this->render->render($material->contentMarkdown),
            $material->reported,
            $material->reportOpen,
            $material->href);
    }

    private function type(Material $material): string {
        $types = ['post' => 'post', 'comment' => 'komentarz', 'microblog' => 'mikroblog'];
        return $types[$material->type];
    }
}

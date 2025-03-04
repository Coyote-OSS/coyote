<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\View;

use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\List\Store\Material;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialResult;

readonly class MaterialList
{
    public function __construct(
        private MarkdownRender $render,
        private Time           $time,
        private MaterialResult $materials,
        private AvatarCdn      $cdn,
    )
    {
    }

    public function total(): int
    {
        return $this->materials->total;
    }

    public function items(): array
    {
        return \array_map($this->viewObject(...), $this->materials->materials);
    }

    private function viewObject(Material $material): MaterialItem
    {
        return new MaterialItem(
            $this->type($material),
            $this->time->date($material->createdAt),
            $this->time->dateOptional($material->parentDeletedAt ?? $material->deletedAt),
            $material->authorUsername,
            $this->cdn->avatar($material->authorImageUrl),
            $this->render->render($material->contentMarkdown),
            $material->reported,
            $material->reportOpen,
            $this->adminUrl($material),
        );
    }

    private function type(Material $material): string
    {
        $types = ['post' => 'post', 'comment' => 'komentarz', 'microblog' => 'mikroblog'];
        return $types[$material->type];
    }

    private function adminUrl(Material $material): ?string
    {
        if ($material->type === 'post') {
            return null;
        }
        if ($material->type === 'comment') {
            return null;
        }
        if ($material->type === 'microblog') {
            return $this->microblogUrl($material);
        }
        return null;
    }

    private function microblogUrl(Material $material): string
    {
        if ($material->parentId) {
            return route('microblog.view', [$material->parentId]) . '#comment-' . $material->id;
        }
        return route('microblog.view', [$material->parentId ?? $material->id]);
    }
}

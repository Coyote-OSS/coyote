<?php
namespace Coyote\Services\Stream\Render;

use Coyote\Services\Adm\UserContent\UserContentItemType;

class MassDelete extends Render {
    public function user(): string {
        return array_get($this->stream, 'object.username');
    }

    protected function excerpt(): string {
        $deletedItems = array_get($this->stream, 'object.deletedItems');
        return "usunięto $deletedItems elementów typu: " . $this->contentType();
    }

    public function contentType(): string {
        return match ($this->itemType()) {
            UserContentItemType::POSTS         => 'posty',
            UserContentItemType::POST_COMMENTS => 'komentarze do postów',
            UserContentItemType::BLOGS         => 'mikroblogi',
            UserContentItemType::BLOG_COMMENTS => 'komentarze pod mikroblogami',
            UserContentItemType::POST_VOTES    => 'głosy oddane na posty',
            UserContentItemType::BLOG_VOTES    => 'głosy oddane na mikroblogi',
            UserContentItemType::FLAGS         => 'raporty od',
            UserContentItemType::MESSAGES      => 'wiadomości prywatne',
            UserContentItemType::JOB_OFFERS    => 'oferty pracy',
        };
    }

    private function itemType(): UserContentItemType {
        $value = array_get($this->stream, 'object.itemTypeValue');
        return UserContentItemType::from($value);
    }
}

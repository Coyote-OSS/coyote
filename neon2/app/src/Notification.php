<?php
namespace Neon2;

readonly class Notification {
    public function __construct(
        public string  $notificationTitle,
        public string  $notificationDate,
        public bool    $notificationHighlighted,
        public string  $contentTitle,
        public string  $contentPreview,
        public string  $contentHref,
        public ?string $actorAvatarUrl,
        public string  $actorInitials,
        public string  $actorProfileHref,
    ) {}
}

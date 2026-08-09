<?php
namespace Web\Projections\ForumJobOffers\ViewModel;

readonly class Tag {
    public function __construct(
        public string  $name,
        public ?string $logoUrl,
    ) {}
}

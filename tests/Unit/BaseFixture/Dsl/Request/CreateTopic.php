<?php
namespace Tests\Unit\BaseFixture\Dsl\Request;

readonly class CreateTopic
{
    public function __construct(
        public string  $categorySlug,
        public string  $title,
        public ?string $discussMode,
    ) {}
}

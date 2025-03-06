<?php
namespace Neon\View;

readonly class JobOffer
{
    public function __construct(
        public string $title,
        public string $companyName,
        public int    $commentsCount,
    ) {}
}

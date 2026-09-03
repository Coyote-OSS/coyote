<?php
namespace Coyote\Projections\ForumJobOffers;

interface Shuffler {
    public function shuffle(array $items): array;
}

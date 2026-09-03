<?php
namespace Coyote\Projections\ForumJobOffers;

use Random\Randomizer;

readonly class RandomShuffler implements Shuffler {
    public function __construct(private Randomizer $randomizer) {}

    public function shuffle(array $items): array {
        return $this->randomizer->shuffleArray($items);
    }
}

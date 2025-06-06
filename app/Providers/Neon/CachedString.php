<?php
namespace Coyote\Providers\Neon;

use Illuminate\Contracts\Cache;

readonly class CachedString {
    public function __construct(private Cache\Repository $cache) {}

    public function cached(string $key, callable $block): string {
        return $this->cache->rememberForever($key, $block);
    }
}

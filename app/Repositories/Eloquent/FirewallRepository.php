<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Firewall;
use Coyote\Repositories\Contracts\FirewallRepositoryInterface;

class FirewallRepository extends Repository implements FirewallRepositoryInterface {
    /**
     * @return string
     */
    public function model() {
        return Firewall::class;
    }

    /**
     * Purge expired firewall entries
     */
    public function purge() {
        $this->model
            ->whereNotNull('expire_at')
            ->where('expire_at', '<=', $this->raw('NOW()'))
            ->delete();
    }
}

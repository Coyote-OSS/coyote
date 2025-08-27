<?php
namespace Coyote\Domain\TempEmail;

use Coyote\TempEmail;

class TempEmailRepository {
    public function findCategory(string $email): TempEmailCategory {
        $domain = $this->emailDomain($email);
        $model = TempEmail::query()->where('domain', $domain)->first();
        if ($model === null) {
            return TempEmailCategory::UNKNOWN;
        }
        return TempEmailCategory::{$model->category};
    }

    private function emailDomain(string $email): string {
        [$username, $domain] = \explode('@', $email);
        return $domain;
    }
}

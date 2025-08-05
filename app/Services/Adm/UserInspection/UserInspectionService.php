<?php
namespace Coyote\Services\Adm\UserInspection;

use Coyote;
use Coyote\Services\Adm\UserInspection;

class UserInspectionService {
    public function __construct(
        private UserFingerprintService $service,
    ) {}

    public function loadFingerprints(int $userId, bool $recognizeOtherUsers): array {
        $fingerprints = $this->service->findFingerprints($userId);
        return [
            $fingerprints,
            $recognizeOtherUsers
                ? $this->fillUserIdToUser($this->service->findUsersIdByFingerprint($fingerprints, $userId))
                : [],
        ];
    }

    /**
     * @param FingerprintUserIdTableItem[] $userIdTablesItems
     */
    private function fillUserIdToUser(array $userIdTablesItems): array {
        return \array_map(
            $this->toUserTableItem(...),
            $userIdTablesItems);
    }

    private function toUserTableItem(FingerprintUserIdTableItem $userIds): FingerprintUserTableItem {
        $users = [];
        foreach ($this->findUserNames($userIds->userIds) as $userId => $username) {
            $users[] = new UserInspection\User($userId, $username);
        }
        return new FingerprintUserTableItem($userIds->fingerprint, $users);
    }

    private function findUserNames(array $userIds): array {
        return Coyote\User::query()->whereIn('id', $userIds)->pluck('name', 'id')->toArray();
    }
}

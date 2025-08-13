<?php
namespace Coyote\Services\Adm;

use Coyote\Models\Multiacc;
use Coyote\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent;

readonly class MultiaccService {
    public function __construct(private Connection $connection) {}

    public function create(): Multiacc {
        return Multiacc::query()->create();
    }

    /**
     * @param string[] $usernames
     */
    public function join(array $usernames): void {
        $this->connection->transaction(function () use ($usernames) {
            $this->attachUsersWithPayload(
                Multiacc::query()->create(),
                $this->findUserIds($usernames),
                ['moderator_id' => auth()->id()]);
        });
    }

    private function attachUsersWithPayload(
        Multiacc $multiacc,
        array    $userIds,
        array    $pivotPayload,
    ): void {
        $attach = [];
        foreach ($userIds as $userId) {
            $attach[$userId] = $pivotPayload;
        }
        $multiacc->users()->syncWithoutDetaching($attach);
    }

    /**
     * @return int[]
     */
    private function findUserIds(array $usernames): array {
        return User::query()
            ->whereIn('name', $usernames)
            ->pluck('id')
            ->all();
    }

    public function addNote(Multiacc $multiacc, string $content): void {
        Multiacc\Note::query()->create([
            'multiacc_id'  => $multiacc->id,
            'moderator_id' => auth()->id(),
            'content'      => $content,
        ]);
    }

    public function findByUsername(string $username): ?Multiacc {
        return Multiacc::query()
            ->whereHas('users', fn(Eloquent\Builder $builder) => $builder
                ->where('name', $username))
            ->first();
    }

    public function include(Multiacc $multiacc, string $username): void {
        $this->attachUsersWithPayload(
            $multiacc,
            $this->findUserIds([$username]),
            ['moderator_id' => auth()->id()]);
    }
}

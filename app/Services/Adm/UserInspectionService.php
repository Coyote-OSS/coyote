<?php
namespace Coyote\Services\Adm;

use Illuminate\Database\Connection;
use Illuminate\Support;
use Illuminate\Support\Facades\DB;

readonly class UserInspectionService {
    public function __construct(
        private Connection $connection,
    ) {}

    /**
     * @return UserInspectionFingerprint[]
     */
    public function findUserFingerprints(int $userId): array {
        return $this->fingerprintRecords($userId)
            ->map(fn(\stdClass $record) => new UserInspectionFingerprint(
                $record->fingerprint,
                $record->times_used,
                $record->last_used))
            ->toArray();
    }

    private function fingerprintRecords(int $userId): Support\Collection {
        return $this->connection->table('streams')
            ->select([
                'fingerprint',
                DB::raw('count(*) as times_used'),
                DB::raw('max(DATE(created_at)) as last_used'),
            ])
            ->where('created_at', '>', now()->subYears(2))
            ->whereRaw("(actor ->> 'id')::integer = ?", [$userId])
            ->whereNotNull('fingerprint')
            ->groupBy('fingerprint')
            ->orderByRaw('max(DATE(created_at)) DESC')
            ->get();
    }
}

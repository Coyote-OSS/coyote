<?php
namespace Coyote\Services\Adm\UserInspection;

use Illuminate\Database\Connection;
use Illuminate\Support;
use Illuminate\Support\Facades\DB;

readonly class UserInspectionService {
    public function __construct(
        private Connection $connection,
    ) {}

    /**
     * @return FingerprintUsage[]
     */
    public function findUserFingerprints(int $userId): array {
        return $this->fingerprintRecords($userId)
            ->map(fn(\stdClass $record) => new FingerprintUsage(
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

    /**
     * @param FingerprintUsage[] $fingerprintUsages
     */
    public function findMultipleUsersByFingerprints(int $userId, array $fingerprintUsages): array {
        return $this->connection
            ->table('streams')
            ->select('streams.fingerprint', DB::raw("json_agg(distinct (actor->>'id')::integer) as users_id"))
            ->whereIn('streams.fingerprint', $this->fingerprints($fingerprintUsages))
            ->where(DB::raw("actor->>'id'"), '!=', $userId)
            ->groupBy('streams.fingerprint')
            ->get()
            ->map(fn(\stdClass $record): FingerprintUsers => new FingerprintUsers(
                $record->fingerprint,
                json_decode($record->users_id)))
            ->toArray();
    }

    /**
     * @param FingerprintUsage[] $fingerprintUsages
     */
    private function fingerprints(array $fingerprintUsages): array {
        return array_map(
            fn(FingerprintUsage $usage) => $usage->fingerprint,
            $fingerprintUsages);
    }
}

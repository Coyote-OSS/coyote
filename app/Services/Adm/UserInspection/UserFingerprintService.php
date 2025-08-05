<?php
namespace Coyote\Services\Adm\UserInspection;

use Illuminate\Database\Connection;
use Illuminate\Support;
use Illuminate\Support\Facades\DB;

readonly class UserFingerprintService {
    public function __construct(
        private Connection $connection,
    ) {}

    /**
     * @return Fingerprint[]
     */
    public function findFingerprints(int $userId): array {
        return $this->fingerprintRecords($userId)
            ->map(fn(\stdClass $record) => new Fingerprint(
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
            ->where('created_at', '>', now()->subMonth())
            ->whereRaw("(actor ->> 'id')::integer = ?", [$userId])
            ->whereNotNull('fingerprint')
            ->groupBy('fingerprint')
            ->orderByRaw('max(DATE(created_at)) DESC')
            ->get();
    }

    /**
     * @param Fingerprint[] $fingerprints
     * @return FingerprintUserIdTableItem[]
     */
    public function findUsersIdByFingerprint(array $fingerprints, int $exceptUserId): array {
        return $this->connection
            ->table('streams')
            ->select('streams.fingerprint', DB::raw("json_agg(distinct (actor->>'id')::integer) as users_id"))
            ->whereIn('streams.fingerprint', $this->fingerprints($fingerprints))
            ->where(DB::raw("actor->>'id'"), '!=', $exceptUserId)
            ->groupBy('streams.fingerprint')
            ->get()
            ->map(fn(\stdClass $record): FingerprintUserIdTableItem => new FingerprintUserIdTableItem(
                $record->fingerprint,
                json_decode($record->users_id)))
            ->toArray();
    }

    /**
     * @param Fingerprint[] $fingerprint
     * @return FingerprintUserIdTableItem[]
     */
    private function fingerprints(array $fingerprint): array {
        return array_map(
            fn(Fingerprint $usage) => $usage->fingerprint,
            $fingerprint);
    }
}

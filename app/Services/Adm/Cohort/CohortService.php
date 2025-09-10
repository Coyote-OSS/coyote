<?php
namespace Coyote\Services\Adm\Cohort;

readonly class CohortService {
    /**
     * @param array<string,string[]> $activeByMonths
     * @return CohortRetention[]
     */
    public function retentionGridStats(int $cohortHorizon, array $activeByMonths): array {
        $result = [];
        foreach ($this->retentionGrid($cohortHorizon, $activeByMonths) as $date => $cohortRetention) {
            $result[] = new CohortRetention(
                $date,
                \array_map($this->countOrNull(...), $cohortRetention));
        }
        return $result;
    }

    /**
     * @param array<string,string[]> $activeByMonths
     * @return array<string,string[][]>
     */
    public function retentionGrid(int $cohortHorizon, array $activeByMonths): array {
        $retainedCount = [];
        foreach ($this->groupCohorts($activeByMonths) as $cohortDate => $cohortUsers) {
            foreach (range(0, $cohortHorizon - 1) as $ageIndex) {
                $targetDate = $this->cohortTargetDate(
                    \array_keys($activeByMonths),
                    $cohortDate,
                    $ageIndex);
                if ($targetDate === null) {
                    $retainedCount[$cohortDate][$ageIndex] = null;
                } else {
                    $retainedCount[$cohortDate][$ageIndex] = \array_values(\array_intersect(
                        $cohortUsers,
                        $activeByMonths[$targetDate]));
                }
            }
        }
        return $retainedCount;
    }

    /**
     * @param string[] $dates
     */
    private function cohortTargetDate(array $dates, string $originDate, int $offset): ?string {
        $dateIndex = \array_search($originDate, $dates);
        if ($dateIndex === false) {
            throw new \Exception();
        }
        return $dates[$dateIndex + $offset] ?? null;
    }

    /**
     * @param array<string,string[]> $activeByMonths
     * @return array<string,string[]>
     */
    public function groupCohorts(array $activeByMonths): array {
        $cohorts = [];
        $firstSeen = $this->firstSeen($activeByMonths);
        foreach ($firstSeen as $userId => $firstSeenInMonth) {
            $cohorts[$firstSeenInMonth][] = $userId;
        }
        return $cohorts;
    }

    /**
     * @param array<string,string[]> $activeByMonths
     * @return array<string,string>
     */
    public function firstSeen(array $activeByMonths): array {
        $firstSeen = [];
        foreach ($activeByMonths as $month => $activeUsers) {
            foreach ($activeUsers as $user) {
                if (!\array_key_exists($user, $firstSeen)) {
                    $firstSeen[$user] = $month;
                }
            }
        }
        return $firstSeen;
    }

    private function countOrNull(?array $users): ?int {
        if ($users === null) {
            return null;
        }
        return \count($users);
    }
}

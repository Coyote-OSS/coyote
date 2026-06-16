<?php
namespace Modules\Campaigns;

class ProportionalInterleaver {
    public function interleave(array $sequences): array {
        $interleaved = [];
        for ($i = 0; $i < $this->sequencesLcm($sequences); $i++) {
            foreach ($sequences as $sequence) {
                if (empty($sequence)) {
                    continue;
                }
                $interleaved[] = $sequence[$i % \count($sequence)];
            }
        }
        return $interleaved;
    }

    private function sequencesLcm(array $sequences): int {
        return \array_reduce($sequences,
            function (int $lcm, array $sequence): int {
                if (empty($sequence)) {
                    return $lcm;
                }
                return $this->lcm($lcm, \count($sequence));
            },
            1);
    }

    private function lcm(int $a, int $b): int {
        return \intDiv($a * $b, $this->gcd($a, $b));
    }

    private function gcd(int $a, int $b): int {
        return $b === 0 ? $a : $this->gcd($b, $a % $b);
    }
}

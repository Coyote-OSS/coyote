<?php
namespace Features\Dsl\Driver\Channel\AcceptanceChannel;

class ScreenshotSequence {
    private ?string $feature = null;
    private ?string $scenario = null;
    private int $count = 1;

    public function __construct(
        private readonly string             $basePath,
        private readonly \DateTimeImmutable $date,
    ) {}

    public function startScenario(string $feature, string $scenario): void {
        $this->feature = $feature;
        $this->scenario = $scenario;
    }

    public function nextPath(string $label): string {
        if ($this->feature === null) {
            throw new \Exception('startScenario() must be called before nextPath().');
        }
        return \sPrintF('%s/%s/%s/%s/%04d-%s.png',
            $this->basePath,
            $this->formattedDate(),
            $this->sanitizeFilename($this->feature),
            $this->sanitizeFilename($this->scenario),
            $this->count++,
            $this->sanitizeFilename($label));
    }

    private function formattedDate(): string {
        return $this->date->format('Y-m-d\TH-i-s');
    }

    private function sanitizeFilename(string $text): string {
        return \trim(\preg_replace('/[^A-Za-z0-9\', -]+/', '', $text));
    }
}

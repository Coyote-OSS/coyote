<?php
namespace Coyote\Modules\Campaigns\Adm\View;

class CampaignStatus {
    private $statuses = [
        'active'         => '',
        'misconfigured'  => 'Data końca lub docelowa liczba wyświetleń nie jest określona.',
        'target-reached' => 'Osiągnięto docelową liczbę wyświetleń.',
        'not-started'    => 'Kampania się nie rozpoczęła, względem daty.',
        'finished'       => 'Kampania się zakończyła, względem daty.',
    ];

    public function __construct(private string $status) {
        if (!\array_key_exists($status, $this->statuses)) {
            throw new \Exception('Invalid campaign status.');
        }
    }

    public function statusText(): string {
        return $this->statuses[$this->status];
    }

    public function active(): bool {
        return $this->status === 'active';
    }
}

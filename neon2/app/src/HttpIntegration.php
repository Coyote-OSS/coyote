<?php
namespace Neon2;

use Neon2\JobBoard\JobOffer;

readonly class HttpIntegration {
    private Web\ViteManifest $vite;

    public function __construct(private \Neon2\JobBoard\JobBoardGate $jobBoard) {
        $this->vite = new \Neon2\Web\ViteManifest(__DIR__ . '/../../web/');
    }

    public function jobBoardView(): string {
        $backendInput = \json_encode(['jobOffers' => $this->jobBoard->listJobOffers()], \JSON_THROW_ON_ERROR);
        $entryUrl = "/neon2/static/{$this->vite->scriptUrl()}";
        return <<<html
            <div id="neonApplication"></div>
            <script type="module" src="$entryUrl"></script>
            <script>var backendInput = $backendInput;</script>
        html;
    }

    public function addAndReturnJobOffer(string $jobOfferTitle, string $jobOfferPlan): JobOffer {
        return $this->jobBoard->addJobOffer($jobOfferTitle, $jobOfferPlan);
    }

    public function initiateJobOfferPayment(int $jobOfferId): void {
        $this->jobBoard->initiateJobOfferPayment($jobOfferId);
    }

    public function editJobOffer(int $jobOfferId, string $jobOfferTitle): void {
        $this->jobBoard->editJobOffer($jobOfferId, $jobOfferTitle);
    }
}

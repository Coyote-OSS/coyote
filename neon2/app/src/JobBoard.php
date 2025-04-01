<?php
namespace Neon2;

readonly class JobBoard
{
    public function addJobOffer(string $title, string $plan): JobOffer
    {
        $jobOffers = $this->listJobOffers();
        $jobOffer = new JobOffer(\count($jobOffers) + 1, $title, $plan === 'free' ? 14 : 30);
        $this->save([...$jobOffers, $jobOffer]);
        return $jobOffer;
    }

    public function editJobOffer(int $jobOfferId, string $jobOfferTitle): void
    {
        $offers = $this->listJobOffers();
        foreach ($offers as $offer) {
            if ($offer->id === $jobOfferId) {
                $offer->title = $jobOfferTitle;
            }
        }
        $this->save($offers);
    }

    /**
     * @return JobOffer[]
     */
    public function listJobOffers(): array
    {
        if (!\file_exists('jobOffers.dat')) {
            return [];
        }
        return \unserialize(\file_get_contents('jobOffers.dat'));
    }

    private function save(array $offers): void
    {
        \file_put_contents('jobOffers.dat', serialize($offers));
    }
}

<?php
namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Domain\RouteVisits;
use Coyote\Domain\StringHtml;
use Coyote\Http\Controllers\Controller;
use Coyote\Plan;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;

class BusinessController extends Controller
{
    public function show(RouteVisits $visits): View
    {
        $agent = new Agent();
        if (!$agent->isRobot($this->request->userAgent())) {
            $visits->visit($this->request->path(), Carbon::now()->toDateString());
        }
        return $this->view('job.business', [
            'plans'        => [
                [
                    'name'           => 'Free',
                    'price'          => 0,
                    'durationInDays' => 14,
                    'bulletPoints'   => [
                        'Za pierwsze ogłoszenie każdego miesiąca',
                        'Tylko dla organizacji pożytku publicznego, uczelni wyższych oraz firm zatrudniających do 5 osób.',
                    ],
                    'createOfferUrl' => $this->createOfferUrl('Free'),
                    'bundleSize'     => 1,
                    'isBundle'       => false,
                ],
                [
                    'name'           => 'Premium',
                    'price'          => 159,
                    'durationInDays' => 30,
                    'bulletPoints'   => [
                        new StringHtml('Gwarancja <strong>1000 wyświetleń</strong> lub zwrot pieniędzy'),
                        new StringHtml('Gwarancja <strong>10,000 wyświetleń</strong> wizytówki lub zwrot pieniędzy'),
                        new StringHtml('<strong>3</strong> automatyczne podbicia'),
                        new StringHtml('<strong>10 lokalizacji</strong>'),
                    ],
                    'createOfferUrl' => $this->createOfferUrl('Premium'),
                    'bundleSize'     => 1,
                    'isBundle'       => false,
                ],
            ],
            'packagePlans' => [
                [
                    'name'           => 'Strategic',
                    'price'          => 119,
                    'fullPrice'      => 357,
                    'durationInDays' => 30,
                    'bulletPoints'   => [
                        new StringHtml('Pakiet <strong>zawiera</strong> wszystkie punkty z ogłoszenia Premium'),
                        new StringHtml('<strong>12 miesięcy</strong> na wykorzystanie'),
                    ],
                    'discount'       => '25%',
                    'createOfferUrl' => $this->createOfferUrl('Strategic'),
                    'bundleSize'     => 3,
                    'isBundle'       => true,
                ],
                [
                    'name'           => 'Growth',
                    'price'          => 99,
                    'fullPrice'      => 495,
                    'durationInDays' => 30,
                    'bulletPoints'   => [
                        new StringHtml('Pakiet <strong>zawiera</strong> wszystkie punkty z ogłoszenia Premium'),
                        new StringHtml('<strong>12 miesięcy</strong> na wykorzystanie'),
                    ],
                    'discount'       => '38%',
                    'createOfferUrl' => $this->createOfferUrl('Growth'),
                    'bundleSize'     => 5,
                    'isBundle'       => true,
                ],
                [
                    'name'           => 'Scale',
                    'fullPrice'      => 1580,
                    'price'          => 79,
                    'durationInDays' => 30,
                    'bulletPoints'   => [
                        new StringHtml('Pakiet <strong>zawiera</strong> wszystkie punkty z ogłoszenia Premium'),
                        new StringHtml('<strong>12 miesięcy</strong> na wykorzystanie'),
                    ],
                    'discount'       => '50%',
                    'createOfferUrl' => $this->createOfferUrl('Scale'),
                    'bundleSize'     => 20,
                    'isBundle'       => true,
                ],
            ],
        ]);
    }

    private function createOfferUrl(string $planName): string
    {
        return route('job.submit', ['plan' => $this->planIdByName($planName)]);
    }

    private function planIdByName(string $planName): int
    {
        return Plan::query()
            ->where('name', $planName)
            ->where('is_active', true)
            ->firstOrFail('id')
            ->id;
    }
}

<?php

namespace Tests\Legacy\IntegrationOld;

use Carbon\Carbon;
use Coyote\Events\PaymentPaid;
use Coyote\Job;
use Coyote\Notifications\SuccessfulPaymentNotification;
use Coyote\Plan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;

class BoostJobsCommandTest extends TestCase {
    use DatabaseTransactions;

    public function setUp(): void {
        parent::setUp();

        Notification::fake();
    }

    public function testBoostJobWithPlusPlan() {
        $plan = Plan::where('name', 'Plus')->get()->first();

        /** @var Job $job */
        $job = factory(Job::class)->create(['plan_id' => $plan->id]);

        event(new PaymentPaid($job->getUnpaidPayment()));

        Notification::assertSentTo([$job->user], SuccessfulPaymentNotification::class);

        $now = now();

        for ($i = 1; $i <= 40; $i++) {
            Carbon::setTestNow($now->addDay());
            $output = $i === 20 ? "Boosting " . $job->title : "Done.";

            $this->artisan('job:boost')
                ->expectsOutput($output);
        }
    }

    public function testBoostJobWithPremiumPlan() {
        $plan = $this->pricingPlan('Premium');

        $job = $this->createJobOffer($plan);

        $start = \DateTimeImmutable::createFromMutable(now());
        $time = now();

        $this->assertTrue($job->is_publish);
        $this->assertTrue($job->is_ads);
        $this->assertTrue($job->is_on_top);
        $this->assertTrue($time->isSameDay($job->boost_at));

        $boostDate = [];
        for ($i = 0; $i < $plan->length; $i++) {
            Carbon::setTestNow($time->addDay());
            $this->artisan('job:boost');
            $boostDate[] = $job->fresh()->boost_at->format('Y-m-d');
        }
        $this->assertSame([
            ...array_fill(0, 9, $start->format('Y-m-d')),
            ...array_fill(0, 10, $start->modify('+10 days')->format('Y-m-d')),
            ...array_fill(0, 11, $start->modify('+20 days')->format('Y-m-d')),
        ], $boostDate);
    }

    private function createJobOffer(Plan $plan): Job {
        $job = factory(Job::class)->create(['plan_id' => $plan->id]);
        event(new PaymentPaid($job->getUnpaidPayment()));
        return $job->fresh();
    }

    private function pricingPlan(string $planName): Plan {
        return Plan::query()
            ->where('name', $planName)
            ->where('is_active', true)
            ->get()
            ->first();
    }
}

<?php
namespace Tests\Legacy\Integration\Seo\Schema\Fixture;

use Coyote\Currency;
use Coyote\Job;
use Coyote\Plan;
use Coyote\User;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\Seo;

trait JobOffer
{
    use Seo\Schema\Fixture\Schema;
    use BaseFixture\Server\Laravel\Transactional;

    function jobOfferSchema(string $title): array
    {
        $job = $this->newJobOffer($title);
        return [
            $this->schema("/Praca/Application/$job->id", 'BreadcrumbList'),
            $job->id,
        ];
    }

    function newJobOffer(string $title): Job
    {
        $currency = new Currency();
        $currency->name = 'irrelevant';
        $currency->save();

        $plan = new Plan;
        $plan->name = 'irrelevant';
        $plan->save();

        $user = new User;
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->save();

        $job = new Job;
        $job->slug = 'irrelevant';
        $job->title = $title;
        $job->currency_id = $currency->id;
        $job->user_id = $user->id;
        $job->plan_id = $plan->id;
        $job->save();
        return $job;
    }
}

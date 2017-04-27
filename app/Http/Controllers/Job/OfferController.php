<?php

namespace Coyote\Http\Controllers\Job;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Factories\FlagFactory;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Firm;
use Coyote\Job;
use Coyote\Services\Elasticsearch\Builders\Job\MoreLikeThisBuilder;

class OfferController extends Controller
{
    use FlagFactory;

    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @param JobRepository $job
     */
    public function __construct(JobRepository $job)
    {
        parent::__construct();

        $this->job = $job;
    }

    /**
     * @param \Coyote\Job $job
     * @return \Illuminate\View\View
     */
    public function index($job)
    {
        $this->breadcrumb->push('Praca', route('job.home'));
        $this->breadcrumb->push($job->title, route('job.offer', [$job->id, $job->slug]));

        $parser = app('parser.job');

        foreach (['description', 'requirements', 'recruitment'] as $name) {
            if (!empty($job->{$name})) {
                $job->{$name} = $parser->parse($job->{$name});
            }
        }

        $tags = $job->tags()->get()->groupBy('pivot.priority');

        if ($job->firm_id) {
            $job->firm->description = $parser->parse((string) $job->firm->description);
        }

        $job->increment('views');
        $job->addReferer(url()->previous());

        if ($this->getGateFactory()->allows('job-delete')) {
            $flag = $this->getFlagFactory()->takeForJob($job->id);
        }

        // search related offers
        $mlt = $this->job->search(new MoreLikeThisBuilder($job))->getSource();

        // calculate enabled features. determines if we should display this element in twig
        $featuresCount = $job
            ->features
            ->filter(function ($item) {
                return $item->pivot->checked;
            })
            ->count();

        return $this->view('job.offer', [
            'rates_list'        => Job::getRatesList(),
            'employment_list'   => Job::getEmploymentList(),
            'employees_list'    => Firm::getEmployeesList(),
            'seniority_list'    => Job::getSeniorityList(),
            'subscribed'        => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false,
            'applied'           => $job->hasApplied($this->userId, $this->guestId),
            'features_count'    => $featuresCount,
            'previous_url'      => $this->request->session()->get('current_url'),
            'payment'           => $this->userId === $job->user_id ? $job->getUnpaidPayment() : null
        ])->with(
            compact('job', 'tags', 'flag', 'mlt')
        );
    }
}

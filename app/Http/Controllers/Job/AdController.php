<?php
namespace Coyote\Http\Controllers\Job;

use Coyote\Job;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\FreeJobsSearchBuilder;
use Coyote\Services\Elasticsearch\Builders\Job\JobSearchBuilder;
use Coyote\Services\Elasticsearch\Builders\Job\PremiumJobsSearchBuilder;
use Coyote\Services\Elasticsearch\Raw;
use Coyote\Services\Elasticsearch\ResultSet;
use Coyote\Services\Geocoder\Location;
use Coyote\Services\Skills\Predictions;
use Coyote\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support;
use Illuminate\Support\Collection;

class AdController extends Controller {
    public function __construct(private JobRepository $job) {
        $this->middleware('geocode');
    }

    public function index(Request $request, Predictions $predictions): Response|View {
        $majorTag = $this->randomMajorTag($predictions->getTags());
        $jobs = $this->jobRecommendation(
            $request,
            $request->attributes->get('geocode'),
            $majorTag);
        if ($jobs->isEmpty()) {
            return \response(status:404);
        }
        /** @var Collection $job */
        foreach ($jobs as $job) {
            $this->increaseAdView($job->get('id'));
        }
        return view('job.ad', [
            'jobs'         => $jobs,
            'selectedTags' => [$majorTag->name],
            'major_tag'    => $majorTag,
        ]);
    }

    private function jobRecommendation(Request $request, ?Location $location, Tag $majorTag): Support\Collection {
        $premiumJobs = $this->premiumJobOffers($request, $location, $majorTag);
        if ($premiumJobs->count() === 4) {
            return $premiumJobs;
        }
        return $premiumJobs->merge($this->freeJobOffers($request, $location, $majorTag))->slice(0, 4);
    }

    private function premiumJobOffers(Request $request, ?Location $location, Tag $majorTag): Collection {
        return $this->search(new PremiumJobsSearchBuilder($request), $location, $majorTag)->getSource();
    }

    private function freeJobOffers(Request $request, ?Location $location, Tag $majorTag): Collection {
        return $this->search(new FreeJobsSearchBuilder($request), $location, $majorTag)->getSource();
    }

    private function search(JobSearchBuilder $builder, ?Location $location, Tag $majorTag): ResultSet {
        $builder->boostLocation($location);
        if ($majorTag->exists) {
            $builder->boostTags(Raw::escape($majorTag->name));
        }
        return $this->job->search($builder);
    }

    private function randomMajorTag(?Eloquent\Collection $tags): Tag {
        if (empty($tags) || !count($tags)) {
            return new Tag();
        }
        return $tags->random();
    }

    private function increaseAdView(int $jobId): void {
        /** @var Job $job */
        $job = Job::query()->find($jobId);
        if ($job) {
            $views = $job->ad_views ?? 0;
            $job->ad_views = $views + 1;
            $job->save();
        }
    }
}

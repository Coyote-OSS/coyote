<?php
namespace Coyote\Http\Controllers\Job;

use Coyote\Job;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Elasticsearch\Builders\Job\AdBuilder;
use Coyote\Services\Elasticsearch\Raw;
use Coyote\Services\Elasticsearch\ResultSet;
use Coyote\Services\Skills\Predictions;
use Coyote\Tag;
use Illuminate\Database\Eloquent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class AdController extends Controller
{
    public function __construct(private JobRepository $job)
    {
        $this->middleware('geocode');
    }

    public function index(Request $request, Predictions $predictions): string
    {
        $builder = new AdBuilder($request);
        $builder->boostLocation($request->attributes->get('geocode'));

        $majorTag = $this->getMajorTag($predictions->getTags());
        if ($majorTag->exists) {
            $builder->boostTags(Raw::escape($majorTag->name));
        }

        /** @var ResultSet $result */
        $result = $this->job->search($builder);
        if (!$result->total()) {
            return false;
        }
        $source = $result->getSource();
        /** @var Collection $jobCollection */
        foreach ($source as $jobCollection) {
            $this->increaseAdView($jobCollection->get('id'));
        }
        return view('job.ad', [
            'jobs'         => $source,
            'selectedTags' => [$majorTag->name],
            'major_tag'    => $majorTag,
        ]);
    }

    private function getMajorTag(?Eloquent\Collection $tags): Tag
    {
        if (empty($tags) || !count($tags)) {
            return new Tag();
        }
        return $tags->random();
    }

    private function increaseAdView(int $id): void
    {
        /** @var Job $job */
        $job = Job::query()->findOrFail($id);
        $views = $job->ad_views ?? 0;
        $job->ad_views = $views + 1;
        $job->save();
    }
}

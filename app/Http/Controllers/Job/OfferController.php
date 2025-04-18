<?php
namespace Coyote\Http\Controllers\Job;

use Carbon\Carbon;
use Coyote\Comment;
use Coyote\Firm;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Presenter\UserPlanBundlePresenter;
use Coyote\Http\Resources\AssetsResource;
use Coyote\Http\Resources\CommentCollection;
use Coyote\Http\Resources\FlagResource;
use Coyote\Http\Resources\JobResource;
use Coyote\Job;
use Coyote\Repositories\Eloquent\JobRepository;
use Coyote\Services\Flags;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\UrlBuilder;
use Illuminate\Support;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(private JobRepository $job)
    {
        parent::__construct();
    }

    public function index(Job $job, UserPlanBundlePresenter $presenter): View
    {
        $this->breadcrumb->push('Praca', route('neon.jobOffer.list'));
        $this->breadcrumb->push($job->title, UrlBuilder::job($job, true));
        $parser = app('parser.job');
        if (!empty($job->description)) {
            $job->description = $parser->parse($job->description);
        }
        if (!empty($job->requirements)) {
            $job->requirements = $parser->parse($job->requirements);
        }
        if (!empty($job->recruitment)) {
            $job->recruitment = $parser->parse($job->recruitment);
        }
        if ($job->firm_id) {
            $job->firm->description = $parser->parse($job->firm->description ?? '');
        }
        $job->addReferer(url()->previous());
        return $this->view('job.offer', [
            'rates_list'        => Job::getRatesList(),
            'employment_list'   => Job::getEmploymentList(),
            'employees_list'    => Firm::getEmployeesList(),
            'seniority_list'    => Job::getSeniorityList(),
            'subscribed'        => $this->userId ? $job->subscribers()->forUser($this->userId)->exists() : false,
            'payment'           => $this->userId === $job->user_id ? $job->getUnpaidPayment() : null,
            'tags'              => $job->tags()->orderBy('priority', 'DESC')->with('category')->get()->groupCategory(),
            'comments'          => new CommentCollection($job->commentsWithChildren)->setOwner($job)->toArray($this->request),
            'applications'      => $this->applications($job),
            'applicationsCount' => $job->applications()->count(),
            'flags'             => $this->flags(),
            'assets'            => AssetsResource::collection($job->firm->assets)->toArray($this->request),
            'subscriptions'     => $this->subscriptions(),
            'emojis'            => Emoji::all(),
            'job'               => $job,
            'jobValidForDays'   => $this->validForDays($job),
            'is_author'         => $job->enable_apply && $job->user_id === auth()->user()?->id,
            'userPlanBundle'    => $presenter->userPlanBundle(),
        ]);
    }

    private function validForDays(Job $job): int
    {
        return Carbon::now()->diffInDays($job->deadline_at) + 1;
    }

    private function applications(Job $job): Support\Collection
    {
        if ($this->userId !== $job->user_id) {
            return Support\Collection::empty();
        }
        return $job->applications()->get();
    }

    private function flags()
    {
        /** @var Flags $flags */
        $flags = resolve(Flags::class);
        $resourceFlags = $flags
            ->fromModels([Job::class, Comment::class])
            ->permission('job-delete')
            ->get();
        return FlagResource::collection($resourceFlags)->toArray($this->request);
    }

    private function subscriptions(): array
    {
        return $this->userId ? JobResource::collection($this->job->subscribes($this->userId))->toArray($this->request) : [];
    }
}

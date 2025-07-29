<?php
namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Models\Asset;
use Coyote\Post;
use Coyote\Repositories\Contracts\PostRepositoryInterface;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Topic;
use Illuminate\Database\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @method string search(array $body)
 * @method void setResponse(string $response)
 * @method $this withTrashed()
 */
class PostRepository extends Repository implements PostRepositoryInterface
{
    public function model(): string
    {
        return \Coyote\Post::class;
    }

    /**
     * @inheritDoc
     */
    public function lengthAwarePagination(Topic $topic, int $page = 0, int $perPage = 10)
    {
        return new LengthAwarePaginator(
            $this->fetchPosts($topic, $page, $perPage),
            $topic->replies + 1, // +1 because we have to count first post in the topic
            $perPage,
            $page,
            ['path' => ' ']);
    }

    private function fetchPosts(Topic $topic, int $page, int $perPage): Eloquent\Collection
    {
        return $this
            ->build(fn(Builder $builder) => $builder
                ->where('posts.topic_id', $topic->id)
                ->forPage($page, $perPage))
            ->with(['user' => fn(Relations\BelongsTo $builder) => $builder->select([
                'users.id',
                'users.name',
                'users.group_name',
                'photo',
                'posts',
                'sig',
                'location',
                'users.created_at',
                'visited_at',
                'deleted_at',
                'is_blocked',
                'is_verified',
                'allow_smilies',
                'allow_count',
                'allow_sig',
                'is_online',
            ])])
            ->with([
                'editor:id,name,is_blocked,deleted_at',
                'comments.user',
                'assets',
            ])
            ->get();
    }

    /**
     * Return page number based on ID of post
     *
     * @param $postId
     * @param $topicId
     * @param int $perPage
     * @return double
     */
    public function getPage($postId, $topicId, $perPage = 10)
    {
        /** @var int $count */
        $count = $this->applyCriteria(function () use ($topicId, $postId) {
            return $this
                ->model
                ->where('topic_id', $topicId)
                ->where('posts.created_at', '<', fn($builder) => $builder->select('created_at')->from('posts')->where('id', $postId))
                ->count();
        });

        return max(0, floor($count / $perPage)) + 1;
    }

    /**
     * @param $topicId
     * @param $markTime
     * @return mixed
     */
    public function getFirstUnreadPostId($topicId, $markTime)
    {
        return $this
            ->model
            ->select(['id'])
            ->where('topic_id', $topicId)
            ->where('created_at', '>', $markTime)
            ->orderBy('id')
            ->limit(1)
            ->value('id');
    }

    /**
     * @param int $userId
     * @param \Coyote\Post $post
     * @return \Coyote\Post
     */
    public function merge($userId, $post)
    {
        /** @var \Coyote\Post $previous */
        $previous = $post->previous();

        $text = join("\n\n", [$previous->text, $post->text]);

        $data = [
            'text'    => $text,
            'title'   => $post->topic->title,
            'tags'    => [],
            'user_id' => $userId,
            'ip'      => request()->ip(),
            'browser' => request()->browser(),
            'host'    => request()->getClientHost(),
        ];

        if ($previous->id == $post->topic->first_post_id) {
            $data['tags'] = $post->topic->tags->pluck('name')->toArray();
        }

        $previous->update(['text' => $text, 'edit_count' => $previous->edit_count + 1, 'editor_id' => $userId]);
        $previous->logs()->create($data);

        $this->app[Asset::class]->where('content_id', $post->id)->where('content_type', Post::class)->update(['content_id' => $previous->id]);
        $this->app[Post\Comment::class]->where('post_id', $post->id)->update(['post_id' => $previous->id]);

        $post->votes()->each(function ($vote) use ($previous) {
            /** @var \Coyote\Post\Vote $vote */
            if (!$previous->votes()->forUser($vote->user_id)->exists()) {
                $previous->votes()->create(array_except($vote->toArray(), ['post_id']));
            }
        });

        $post->delete();

        return $previous;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function takeRatesForUser($userId)
    {
        return $this
            ->model
            ->select([
                'posts.id AS post_id',
                'topics.title',
                'posts.topic_id',
                'posts.created_at',
                'post_votes.created_at AS voted_at',
                'topics.slug AS topic_slug',
                'forums.slug AS forum_slug',
                'users.id AS user_id',
                'users.name AS user_name',
            ])
            ->join('post_votes', 'post_votes.post_id', '=', 'posts.id')
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->join('forums', 'forums.id', '=', 'posts.forum_id')
            ->join('users', 'users.id', '=', 'post_votes.user_id')
            ->where('posts.user_id', $userId);
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function takeAcceptsForUser($userId)
    {
        return $this
            ->model
            ->select([
                'posts.id AS post_id',
                'topics.title',
                'posts.topic_id',
                'posts.created_at',
                'post_accepts.created_at AS accepted_at',
                'topics.slug AS topic_slug',
                'forums.slug AS forum_slug',
                'users.id AS user_id',
                'users.name AS user_name',
            ])
            ->join('post_accepts', 'post_accepts.post_id', '=', 'posts.id')
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->join('forums', 'forums.id', '=', 'posts.forum_id')
            ->join('users', 'users.id', '=', 'post_accepts.user_id')
            ->where('posts.user_id', $userId);
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function takeStatsForUser($userId)
    {
        $this->applyCriteria();

        return $this
            ->model
            ->select([
                'posts.forum_id',
                'forums.slug',
                'forums.name',
                $this->raw('COUNT(posts.id) AS posts_count'),
                $this->raw('SUM(score) AS votes_count'),
            ])
            ->join('forums', 'forums.id', '=', 'posts.forum_id')
            ->where('posts.user_id', $userId)
            ->groupBy('posts.forum_id')
            ->groupBy('forums.slug')
            ->groupBy('forums.name');
    }

    /**
     * @inheritdoc
     */
    public function pieChart($userId)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->select(['forums.name', $this->raw('COUNT(*)')])
            ->where('user_id', $userId)
            ->join('forums', 'forums.id', '=', 'posts.forum_id')
            ->groupBy(['forum_id', 'forums.name'])
            ->orderBy($this->raw('COUNT(*)'), 'DESC')
            ->get()
            ->pluck('count', 'name');

        $this->resetModel();

        if (count($result) > 10) {
            $others = $result->splice(10);
            $result['Pozostałe'] = $others->sum();
        }

        return $result->toArray();
    }

    /**
     * @inheritdoc
     */
    public function lineChart($userId)
    {
        $dt = new Carbon('-6 months');
        $interval = $dt->diffInMonths(new Carbon());

        $sql = $this
            ->model
            ->selectRaw('extract(MONTH FROM created_at) AS month, extract(YEAR FROM created_at) AS year, COUNT(*) AS count')
            ->whereRaw("user_id = $userId")
            ->whereRaw("created_at >= '$dt'")
            ->groupBy('year')
            ->groupBy('month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $result = [];
        foreach ($sql as $row) {
            $result[sprintf('%d-%02d', $row['year'], $row['month'])] = $row->toArray();
        }

        $rowset = [];

        for ($i = 0; $i <= $interval; $i++) {
            $key = $dt->format('Y-m');
            $months = ['styczeń', 'luty', 'marzec', 'kwiecień', 'maj', 'czerwiec', 'lipiec', 'sierpień', 'wrzesień', 'październik', 'listopad', 'grudzień'];
            $label = $months[$dt->month - 1] . ' ' . $dt->format('Y');

            if (!isset($result[$key])) {
                $rowset[] = ['count' => 0, 'year' => $dt->format('Y'), 'month' => $dt->format('n'), 'label' => $label];
            } else {
                $rowset[] = array_merge($result[$key], ['label' => $label]);
            }

            $dt->addMonth();
        }

        return $rowset;
    }

    /**
     * @inheritdoc
     */
    public function countComments($userId)
    {
        return $this
            ->app
            ->make(Post\Comment::class)
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * @inheritdoc
     */
    public function countReceivedVotes($userId)
    {
        return $this
            ->model
            ->selectRaw('SUM(score) AS votes')
            ->where('user_id', $userId)
            ->value('votes');
    }

    /**
     * @inheritdoc
     */
    public function countGivenVotes($userId)
    {
        return $this
            ->app
            ->make(Post\Vote::class)
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    private function build(callable $callback)
    {
        $sub = $this->toSql($callback($this->buildSubquery()));

        $this->applyCriteria();

        $sql = $this
            ->model
            ->addSelect([// addSelect() instead of select() to retrieve extra columns in criteria
                'posts.*',
                'pa.user_id AS is_accepted',
            ])
            ->from($this->raw("($sub) AS posts"))
            ->leftJoin('post_accepts AS pa', 'pa.post_id', '=', 'posts.id')
            ->orderBy('posts.created_at'); // <-- make sure that posts are in the right order!

        $this->resetModel();

        return $sql;
    }

    /**
     * Subquery for better performance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildSubquery()
    {
        $sql = clone $this->model;

        foreach ($this->criteria as $criteria) {
            // include only this criteria to fetch deleted posts (only for users with special access)
            if ($criteria instanceof WithTrashed) {
                $sql = $criteria->apply($sql, $this);
            }
        }

        return $sql
            ->selectRaw('posts.*')
            ->orderBy('posts.created_at');
    }
}

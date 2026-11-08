<?php
namespace Coyote\Domain\Homepage;

use Coyote\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;

/**
 * Additional facts about topics listed on the homepage,
 * fetched in one query per fact for all listed topics.
 */
readonly class TopicSummaries
{
    public function __construct(private Connection $connection) {}

    /**
     * Sum of votes on all posts in each topic.
     * @param int[] $topicIds
     * @return array<int, int> topic id => likes
     */
    public function likes(array $topicIds): array
    {
        if (empty($topicIds)) {
            return [];
        }
        return $this->connection->table('posts')
            ->whereIn('topic_id', $topicIds)
            ->whereNull('deleted_at')
            ->groupBy('topic_id')
            ->selectRaw('topic_id, SUM(score) AS likes')
            ->pluck('likes', 'topic_id')
            ->map(fn($likes) => (int)$likes)
            ->all();
    }

    /**
     * Users who posted in each topic, those with most posts first.
     * @param int[] $topicIds
     * @return array<int, array{users: array[], more: int}> topic id => participants
     */
    public function participants(array $topicIds, int $limit): array
    {
        if (empty($topicIds)) {
            return [];
        }
        $rows = $this->connection->table('posts')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->whereIn('posts.topic_id', $topicIds)
            ->whereNull('posts.deleted_at')
            ->whereNull('users.deleted_at')
            ->where('users.is_incognito', false)
            ->groupBy('posts.topic_id', 'posts.user_id')
            ->orderBy('posts.topic_id')
            ->orderByDesc(new Expression('COUNT(*)'))
            ->orderBy(new Expression('MIN(posts.id)')) // ties: who joined the discussion first
            ->get(['posts.topic_id', 'posts.user_id', new Expression('COUNT(*) AS posts')]);

        $users = User::query()
            ->findMany($rows->pluck('user_id')->unique()->all(), ['id', 'name', 'photo'])
            ->keyBy('id');

        $rows = $rows->filter(fn($row) => $users->has($row->user_id)); // e.g. blocked accounts
        $participants = [];
        foreach ($rows->groupBy('topic_id') as $topicId => $topicRows) {
            // "+1" takes as much room as an avatar, so show that one person instead
            $shown = $topicRows->count() === $limit + 1 ? $limit + 1 : $limit;
            $participants[$topicId] = [
                'users' => $topicRows->take($shown)->map(fn($row) => [
                    'name'       => $users[$row->user_id]->name,
                    'avatarUrl'  => $users[$row->user_id]->photo->getFilename(),
                    'profileUrl' => route('profile', [$row->user_id]),
                    'posts'      => (int)$row->posts,
                ])->values()->all(),
                'more'  => \max(0, $topicRows->count() - $shown),
            ];
        }
        return $participants;
    }
}

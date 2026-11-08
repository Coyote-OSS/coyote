<?php
namespace Coyote\Domain\Online;

use Coyote\Group;
use Coyote\User;
use Illuminate\Contracts\Cache;

/**
 * Members list for the homepage, in the spirit of a chat contact list:
 * users online right now first, then users seen most recently.
 */
readonly class CommunityMembers
{
    public function __construct(private Cache\Repository $cache) {}

    /**
     * Online users first, followed by a few users who were online recently, but aren't anymore.
     * Within each group, users followed by the viewer come first.
     * @param int[] $followedIds
     * @return array{online: array[], recent: array[], onlineHidden: int}
     */
    public function members(Viewers $viewers, int $onlineLimit, int $recentLimit, array $followedIds = []): array
    {
        $online = $this->followedFirst(\array_map($this->onlineMember(...), $viewers->users), $followedIds);
        $onlineIds = \array_column($online, 'id');
        $recent = \array_filter(
            $this->recentlySeen($recentLimit + \count($online)),
            fn(array $member) => !\in_array($member['id'], $onlineIds, true));
        return [
            'online'       => \array_slice($online, 0, $onlineLimit),
            'onlineHidden' => \max(0, \count($online) - $onlineLimit),
            'recent'       => $this->followedFirst(\array_slice(\array_values($recent), 0, $recentLimit), $followedIds),
        ];
    }

    private function followedFirst(array $members, array $followedIds): array
    {
        foreach ($members as $index => &$member) {
            $member['followed'] = \in_array($member['id'], $followedIds);
            $member['order'] = $index; // natural position, so the list can be re-sorted in the browser
        }
        // usort() is stable, so otherwise the order (e.g. most recently seen first) is kept
        \usort($members, fn(array $a, array $b) => $b['followed'] <=> $a['followed']);
        return $members;
    }

    private function onlineMember(ViewerUser $user): array
    {
        return [
            'id'         => $user->vCardId,
            'name'       => $user->name,
            'avatarUrl'  => $user->avatarUrl,
            'profileUrl' => $user->profileUrl,
            'group'      => $user->groupShortName,
            'visitedAt'  => null,
        ];
    }

    private function recentlySeen(int $limit): array
    {
        return $this->cache->remember('homepage:recently-seen:' . $limit, 60, fn() => User::query()
            ->select(['id', 'name', 'photo', 'group_name', 'visited_at'])
            ->where('is_incognito', false)
            ->where('is_blocked', 0)
            ->whereNotNull('visited_at')
            ->orderByDesc('visited_at')
            ->limit($limit)
            ->get()
            ->map(fn(User $user) => [
                'id'         => $user->id,
                'name'       => $user->name,
                'avatarUrl'  => $user->photo->getFilename(),
                'profileUrl' => route('profile', [$user->id]),
                'group'      => $user->group_name ? Group::groupShortName($user->group_name) : null,
                'visitedAt'  => $user->visited_at->toIso8601String(),
            ])
            ->all());
    }
}

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
     * Fixed-size list: online users first, the remaining slots
     * are filled with users seen most recently.
     * @return array{online: array[], recent: array[], onlineHidden: int}
     */
    public function members(Viewers $viewers, int $limit): array
    {
        $online = \array_map($this->onlineMember(...), $viewers->users);
        $onlineShown = \array_slice($online, 0, $limit);
        $recentSlots = $limit - \count($onlineShown);
        $onlineIds = \array_column($online, 'id');
        $recent = \array_filter(
            $recentSlots > 0 ? $this->recentlySeen($limit * 2) : [],
            fn(array $member) => !\in_array($member['id'], $onlineIds, true));
        return [
            'online'       => $onlineShown,
            'onlineHidden' => \count($online) - \count($onlineShown),
            'recent'       => \array_slice(\array_values($recent), 0, $recentSlots),
        ];
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
            ->where('is_blocked', false)
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

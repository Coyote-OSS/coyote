<?php
namespace Coyote\Providers\Neon;

use Carbon\Carbon;
use Coyote;
use Coyote\Http\Resources\NotificationResource;
use Coyote\Repositories\Eloquent\NotificationRepository;
use Illuminate\Database\Eloquent;
use Neon2;

class NavigationUserNotificationPresenter {
    /**
     * @return Neon2\Notification[]
     */
    public function notifications(int $offset, $limit): array {
        $result = [];
        foreach ($this->notificationResources($offset, $limit) as $notification) {
            $result[] = new Neon2\Notification(
                $notification['headline'],
                $this->formatDate($notification['created_at']),
                !$notification['is_clicked'],
                $notification['subject'],
                $notification['excerpt'] ?? '',
                route('user.notifications.url', [$notification['id']]),
                $notification['photo'] ?: null,
                $notification['initials'],
                $notification['user_id'] === null
                    ? '' // Some notification resources don't provide user id, due to a bug, (e.g. application for a job offer)
                    : \route('profile', [$notification['user_id']]),
            );
        }
        return $result;
    }

    /**
     * @return array[]
     */
    private function notificationResources(int $offset, int $limit): array {
        return NotificationResource::collection($this->userNotifications($offset, $limit))->toArray(request());
    }

    /**
     * @return Coyote\Notification[]|Eloquent\Collection
     */
    private function userNotifications(int $offset, int $limit): Eloquent\Collection {
        /** @var NotificationRepository $notifications */
        $notifications = app(NotificationRepository::class);
        /** @var Coyote\User $user */
        $user = auth()->user();
        return $notifications->takeForUser($user->id, $limit, $offset);
    }

    private function formatDate(string $dateIso8601): string {
        return Carbon::parse($dateIso8601)->format('d F Y H:i');
    }
}

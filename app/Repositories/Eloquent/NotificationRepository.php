<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Notification;
use Coyote\Notification\Setting;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @package Coyote\Repositories\Eloquent
 */
class NotificationRepository extends Repository implements NotificationRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Notification::class;
    }

    /**
     * @param int $userId
     * @param int $perPage
     * @return mixed
     */
    public function lengthAwarePaginate($userId, $perPage = 20)
    {
        return $this->prepare($userId)->paginate($perPage);
    }

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function takeForUser($userId, $limit = 10, $offset = 0)
    {
        return $this->prepare($userId)->take($limit)->skip($offset)->get();
    }

    /**
     * Mark notifications as read
     *
     * @use Coyote\Http\Controllers\User\NotificationsController
     * @param array $id
     */
    public function markAsRead(array $id)
    {
        $this->model->whereIn('id', $id)->update(['read_at' => Carbon::now()]);
    }

    /**
     * Find notification by url and mark it as read
     *
     * @param int $userId
     * @param mixed $model
     */
    public function markAsReadByModel($userId, $model)
    {
        $this
            ->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->where('content_id', $model->id)
            ->where('content_type', class_basename($model))
            ->update(['read_at' => Carbon::now()]);
    }

    /**
     * Build query for repository
     *
     * @param int $userId
     * @return mixed
     */
    private function prepare($userId)
    {
        return $this
                ->model
                ->select(['notifications.*', 'notification_types.headline'])
                ->where('user_id', $userId)
                ->with(['senders' => function (HasMany $sql) {
                    $sql
                        ->select([
                            'notification_id',
                            'user_id',
                            $this->raw('COALESCE(users.name, notification_senders.name) AS name'),
                            'photo',
                            'is_blocked',
                            $this->raw('users.deleted_at IS NULL AS is_active')
                        ])
                        ->orderBy('notification_senders.id');
                }])
                ->join('notification_types', 'notification_types.id', '=', 'type_id')
                ->orderBy('notifications.created_at', 'DESC');
    }

    /**
     * Gets public notification types
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function notificationTypes()
    {
        return $this
            ->app
            ->make(Notification\Type::class)
            ->select()
            ->where('is_public', 1)
            ->orderBy('notification_types.id')
            ->get();
    }

    /**
     * @param int $userId
     * @param array $data
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function updateSettings($userId, array $data)
    {
        $model = $this->app->make(Setting::class);

        foreach ($data as $id => $value) {
            $model->where('user_id', $userId)->where('id', $id)->update(['is_enabled' => $value]);
        }
    }

    public function purge(): void
    {
        $this->model->whereNotNull('read_at')->where('is_clicked', true)->where('created_at', '<', now()->subYear())->delete();
    }
}

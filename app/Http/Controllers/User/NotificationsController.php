<?php
namespace Coyote\Http\Controllers\User;

use Carbon;
use Coyote\Events\NotificationRead;
use Coyote\Http\Resources\NotificationResource;
use Coyote\Notification;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class NotificationsController extends BaseController {
    public function __construct(private NotificationRepositoryInterface $notification) {
        parent::__construct();
    }

    public function index(): View {
        $this->breadcrumb->push('Powiadomienia', route('user.notifications'));

        $pagination = $this->notification->lengthAwarePaginate($this->userId);
        $this->markAsReadAndCount($pagination);
        $pagination->setCollection(
            collect(NotificationResource::collection($pagination->getCollection())->toArray($this->request)),
        );

        return $this->view('user.notifications.home', [
            'pagination' => $pagination,
        ]);
    }

    public function settings(): View {
        $this->breadcrumb->push('Ustawienia powiadomień', route('user.notifications.settings'));
        $groupBy = $this->auth->notificationSettings()
            ->whereNot('channel', 'push')
            ->get()
            ->sortBy('channel')
            ->groupBy('type_id');

        return $this->view('user.notifications.settings', [
            'groups'   => $this->notification->notificationTypes()->groupBy('category'),
            'settings' => $groupBy,
            'channels' => Notification::getChannels(),
        ]);
    }

    public function save(Request $request): RedirectResponse {
        $this->notification->updateSettings($this->userId, $request->input('settings'));

        return back()->with('success', 'Zmiany zostały zapisane');
    }

    public function ajax(Request $request): JsonResponse {
        $unread = $this->auth->notifications_unread;
        $offset = $request->query('offset', 0);

        $notifications = $this->notification->takeForUser($this->userId, max(10, $unread), $offset);
        $unread -= $this->markAsReadAndCount($notifications);

        return response()->json([
            'count'         => $unread,
            'notifications' => $this->formatNotificationsHeadline($notifications),
        ]);
    }

    private function formatNotificationsHeadline(Collection $notifications): array {
        return array_filter(NotificationResource::collection($notifications)->toArray($this->request));
    }

    public function delete(string $id): void {
        $this->notification->delete($id);
    }

    /**
     * Marks all alerts as read
     */
    public function markAllAsRead(): void {
        if ($this->auth->notifications_unread) {
            $this->notification->where('user_id', $this->userId)->whereNull('read_at')->update([
                'read_at' => Carbon\Carbon::now(),
            ]);
        }

        $this->notification->where('user_id', $this->userId)->update(['is_clicked' => true]);
    }

    /**
     * @deprecated
     */
    public function url(string $id): Response|RedirectResponse {
        if (!$this->isUuid($id)) {
            return response('', 400);
        }
        /** @var Notification $notification */
        $notification = $this->notification->findOrFail($id, ['id', 'url', 'read_at', 'user_id', 'is_clicked']);

        $notification->is_clicked = true;

        if (!$notification->read_at) {
            $notification->read_at = Carbon\Carbon::now();

            broadcast(new NotificationRead($notification));
        }

        $notification->save();

        return redirect()->to($notification->url);
    }

    private function isUuid(string $uuid): bool {
        return \preg_match("/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i", $uuid);
    }

    public function redirectToUrl(): RedirectResponse {
        $path = \urlDecode($this->request->get('path'));

        if (!$this->userId) {
            return redirect()->to($path);
        }

        /** @var Notification $notification */
        $notification = $this->auth->notifications()->where('url', $path)->first();

        if ($notification) {
            $notification->is_clicked = true;
            if (!$notification->read_at) {
                $notification->read_at = now();
                broadcast(new NotificationRead($notification));
            }
            $notification->save();
        }
        return redirect()->to($path);
    }

    private function markAsReadAndCount(Collection|Paginator $notifications): int {
        $unreadNotifications = $notifications
            ->filter(fn(Notification $notification) => $notification->read_at === null)
            ->pluck('id')
            ->all();
        if (!empty($unreadNotifications)) {
            $this->notification->markAsRead($unreadNotifications);
        }
        return count($unreadNotifications);
    }
}

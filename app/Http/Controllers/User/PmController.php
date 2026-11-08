<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Events\PmCreated;
use Coyote\Events\PmRead;
use Coyote\Http\Requests\PmRequest;
use Coyote\Http\Resources\PmResource;
use Coyote\Notifications\PmCreatedNotification;
use Coyote\Pm;
use Coyote\Repositories\Contracts\NotificationRepositoryInterface as NotificationRepository;
use Coyote\Repositories\Contracts\PmRepositoryInterface as PmRepository;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Services\Parser\Factories\PmFactory;
use Coyote\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use function app;

class PmController extends BaseController
{
    private UserRepository $user;
    private NotificationRepository $notification;
    private PmRepository $pm;

    public function __construct(UserRepository $user, NotificationRepository $notification, PmRepository $pm)
    {
        parent::__construct();

        $this->user = $user;
        $this->notification = $notification;
        $this->pm = $pm;

        $this->breadcrumb->push('Wiadomości prywatne', route('user.pm'));
    }

    public function index(Request $request): JsonResponse|View
    {
        $pm = $this->pm->lengthAwarePaginate($this->userId);
        $messages = PmResource::collection(collect($pm->items()));
        $result = [
            'messages'     => $messages->toArray($this->request),
            'per_page'     => $pm->perPage(),
            'total'        => $pm->total(),
            'current_page' => $pm->currentPage(),
        ];
        if ($request->wantsJson()) {
            return response()->json($result);
        }
        return $this->view('user.pm.home')->with($result);
    }

    public function show(Pm $pm, Request $request): View
    {
        $this->authorize('show', $pm);

        $messages = PmResource::collection($this->pm->conversation(
            $this->userId,
            $pm->author_id,
            10,
            (int)$request->query('offset', 0)));

        $this->markAllAsRead($pm->author);

        return $this->view('user.pm.show', [
            'pm'        => $pm,
            'messages'  => $messages,
            'recipient' => $pm->author,
            'emojis'    => Emoji::all(),
        ]);
    }

    public function infinity(Request $request): ResourceCollection
    {
        return PmResource::collection($this->pm->conversation(
            $this->userId,
            (int)$request->input('author_id'),
            10,
            (int)$request->query('offset', 0)));
    }

    public function inbox(): ResourceCollection
    {
        $pm = $this->pm->groupByAuthor($this->userId);
        PmResource::withoutWrapping();
        return PmResource::collection($pm);
    }

    public function submit(Request $request): View|RedirectResponse
    {
        $recipient = $request->has('to')
            ? $this->user->findByName($request->input('to'))
            : null;
        if ($recipient) {
            $conversation = $this->existingConversation($recipient);
            if ($conversation) {
                return redirect()->route('user.pm.show', [$conversation->id]);
            }
        }
        $this->breadcrumb->push('Napisz wiadomość', route('user.pm.submit'));
        return $this->view('user.pm.show', [
            'recipient' => $recipient ?? new \stdClass(),
            'messages'  => [],
            'emojis'    => Emoji::all(),
        ]);
    }

    private function existingConversation(User $recipient): ?Pm
    {
        return Pm::query()
            ->where('user_id', $this->userId)
            ->where('author_id', $recipient->id)
            ->orderByDesc('id')
            ->first();
    }

    public function preview(Request $request): Response
    {
        $text = (string)$request->get('text');
        /** @var PmFactory $factory */
        $factory = app('parser.pm');
        return response($factory->parse($text));
    }

    public function save(PmRequest $request): PmResource
    {
        $recipient = $this->user->findByName($request->input('recipient'));
        $pm = $this->transaction(function () use ($request, $recipient) {
            $result = $this->pm->submit($this->auth, $request->all() + ['author_id' => $recipient->id]);
            $result[Pm::SENTBOX]->assets()->sync($request->input('assets'));
            return $result;
        });
        event(new PmCreated($pm[Pm::INBOX]));
        $recipient->notify(new PmCreatedNotification($pm[Pm::INBOX]));
        PmResource::withoutWrapping();
        return new PmResource($pm[Pm::SENTBOX]);
    }

    public function delete(Pm $pm): void
    {
        $this->authorize('show', $pm);
        $pm->delete();
    }

    public function mark(Pm $pm): JsonResponse
    {
        $this->authorize('show', $pm);
        $this->markAllAsRead($pm->author);
        $this->auth->refresh();
        return response()->json(['count' => $this->auth->pm_unread]);
    }

    private function markAllAsRead(User $author): void
    {
        $result = $this->pm->getNotRead($this->userId, $author->id);

        foreach ($result as $pm) {
            $this->markAsRead($pm);
        }

        if (count($result)) {
            $this->request->attributes->set('pm_unread', --$this->auth->pm_unread);
        }
    }

    private function markAsRead(Pm $pm): void
    {
        if ($pm->read_at) {
            return;
        }
        // database trigger will decrease pm counter in "users" table.
        $this->pm->markAsRead($pm->text_id);
        // IF we have unread alert that is connected with that message... then we also have to mark it as read
        if ($this->auth->notifications_unread) {
            $this->notification->markAsReadByModel($this->userId, $pm);
        }
        event(new PmRead($pm));
    }

    public function trash(int $authorId): void
    {
        $pm = $this->pm->findWhere(['user_id' => $this->userId, 'author_id' => $authorId]);
        abort_if($pm->count() == 0, 404);

        $this->pm->trash($this->userId, $authorId);
    }
}

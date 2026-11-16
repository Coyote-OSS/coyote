<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\Wiki\CommentForm;
use Coyote\Notifications\Wiki\CommentedNotification;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Objects\Comment as Stream_Comment;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;
use Illuminate\Contracts\Notifications\Dispatcher;

class CommentController extends Controller
{
    /**
     * @param Dispatcher $dispatcher
     * @param \Coyote\Wiki $wiki
     * @param int|null $id
     * @return \Illuminate\View\View
     */
    public function save(Dispatcher $dispatcher, $wiki, $id = null)
    {
        /** @var \Coyote\Wiki\Comment $comment */
        $comment = $wiki->comments()->findOrNew($id);

        $form = $this->createForm(CommentForm::class, $comment);
        $form->validate();

        $comment->fill($form->all());

        if (!$comment->exists) {
            $comment->user_id = $this->userId;
            $comment->ip = $form->getRequest()->ip();
        } else {
            $this->authorize('update', $comment);
        }

        $this->transaction(function () use ($wiki, $dispatcher, $comment) {
            // before creating new record we decide whether to add user to subscribers list or not.
            $subscribe = $this->auth->allow_subscribe
                && !$comment->exists && !$comment->wasUserInvolved($wiki->id, $this->userId);
            $comment->save();

            if ($comment->wasRecentlyCreated) {
                $subscribers = $wiki->subscribers()->with('user')->get()->pluck('user');

                $dispatcher->send(
                    $subscribers->exceptUser($this->auth),
                    new CommentedNotification($comment)
                );

                // we DO NOT want to add another row into the table. we MUST check whether user is already
                // on subscribers list or not.
                if ($subscribe && !$subscribers->contains('id', $this->userId)) {
                    $wiki->subscribers()->create(['user_id' => $this->userId]);
                }
            }

            stream(
                $comment->wasRecentlyCreated ? Stream_Create::class : Stream_Update::class,
                (new Stream_Comment())->map($wiki, $comment),
                (new Stream_Wiki())->map($wiki)
            );
        });

        return view('wiki.partials.comment', ['comment' => $comment, 'wiki' => $wiki, 'form' => $form]);
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($wiki, $id)
    {
        /** @var \Coyote\Wiki\Comment $comment */
        $comment = $wiki->comments()->findOrFail($id);
        $this->authorize('delete', [$comment]);

        $this->transaction(function () use ($comment, $wiki) {
            $comment->delete();

            stream(
                Stream_Delete::class,
                (new Stream_Comment())->map($wiki, $comment),
                (new Stream_Wiki())->map($wiki)
            );
        });

        return redirect()->back()->with('success', 'Komentarz został usunięty.');
    }
}

<?php
namespace Coyote\Http\Controllers\Chat;

use Coyote\Events\ChatMessage;
use Coyote\Http\Controllers\Controller;
use Coyote\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ChatController extends Controller {
    public function index(): View {
        Gate::authorize('alpha-access');
        return $this->view('chat.home', [
            'websocketSubscribeCommand' => 'subscribe:global-chat',
        ]);
    }

    public function sendMessage(): void {
        /** @var User $user */
        $user = auth()->user();
        event(new ChatMessage(
            request()->get('message'),
            auth()->id(),
            $user->name));
    }
}

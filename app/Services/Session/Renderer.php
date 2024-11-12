<?php
namespace Coyote\Services\Session;

use Coyote\Domain\Online\SessionRepository;
use Coyote\Domain\Online\Viewers;
use Coyote\Domain\Online\ViewersStore;
use Coyote\Domain\Spacer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Renderer
{
    private Spacer $spacer;

    public function __construct(
        private SessionRepository $session,
        private ViewersStore      $store,
        private Request           $request,
    )
    {
        $this->spacer = new Spacer(8);
    }

    public function render(string $requestUri, bool $local, bool $iconVisible = true): View
    {
        $viewers = $this->sessionViewers($requestUri);

        [$users, $superfluous] = $this->spacer->fitInSpace($viewers->usersWithoutGroup());

        return view('components.viewers', [
            'local'             => $local,
            'iconVisible'       => $iconVisible,
            'guestsCount'       => $viewers->guestsCount,
            'usersCount'        => \count($viewers->users),
            'title'             => $local
                ? 'Aktualnie na tej stronie'
                : 'Użytkownicy online',
            'usersWithGroup'    => $viewers->usersWithGroup(),
            'usersWithoutGroup' => $users,
            'superfluousCount'  => $superfluous,
        ]);
    }

    public function sessionViewers(string $requestUri): Viewers
    {
        $sessions = $this->session->sessionsIn($requestUri);
        if ($this->isUserLogged()) {
            $sessions = $sessions->coalesceUser($this->loggedUserId());
        } else {
            $sessions = $sessions->coalesceGuest();
        }
        return $this->store->viewers($sessions);
    }

    private function isUserLogged(): bool
    {
        return !!$this->request->user();
    }

    private function loggedUserId()
    {
        return $this->request->user()->id;
    }
}

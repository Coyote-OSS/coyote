<?php
namespace Coyote\Http\Composers;

use Coyote\Domain\Initials;
use Coyote\Services\Forum\UserDefined;
use Coyote\Services\JwtToken;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InitialStateComposer
{
    private string $jwtToken;

    public function __construct(
        private Request     $request,
        private UserDefined $userDefined,
        JwtToken            $jwtToken)
    {
        if ($request->user()) {
            $this->jwtToken = $jwtToken->token($request->user());
        }
    }

    public function compose(View $view): void
    {
        $view->with('__INITIAL_STATE', \json_encode($this->initialState()));
        $view->with('__WS_URL', $this->websocketUrl());
    }

    private function initialState(): array
    {
        return \array_merge(
            $this->request->attributes->all(),
            [
                'user' => [
                    'notifications_unread' => 0,
                    'pm_unread'            => 0,
                ],
            ],
            $this->registerUserModel(),
            $this->registerFollowers(),
        );
    }

    public function websocketUrl(): ?string
    {
        if (config('services.ws.host') && $this->request->user()) {
            $schema = $this->request->isSecure() ? 'wss' : 'ws';
            $host = config('services.ws.host');
            $port = config('services.ws.port');
            if ($port) {
                $uri = "$schema://$host:$port";
            } else {
                $uri = "$schema://$host";
            }
            return "$uri/realtime?token=$this->jwtToken";
        }
        return null;
    }

    private function registerUserModel(): array
    {
        if (empty($this->request->user())) {
            return [];
        }
        /** @var User $user */
        $user = $this->request->user();
        return [
            'user' => [
                'id'                   => $user->id,
                'name'                 => $user->name,
                'date_format'          => $this->mapFormat($user->date_format),
                'token'                => $this->jwtToken,
                'notifications_unread' => $user->notifications_unread,
                'pm_unread'            => $user->pm_unread,
                'created_at'           => $user->created_at->toIso8601String(),
                'photo'                => (string)$user->photo->url(),
                'is_sponsor'           => $user->is_sponsor,
                'postCommentStyle'     => $user->guest->settings['postCommentStyle'] ?? 'legacy',
                'initials'             => (new Initials)->of($user->name),
            ],
        ];
    }

    private function registerFollowers(): array
    {
        /** @var User $user */
        $user = $this->request->user();
        if (empty($user)) {
            return [];
        }
        return [
            'followers' => $this->userDefined->followers($user),
        ];
    }

    private function mapFormat(string $format): string
    {
        $values = [
            'dd-MM-yyyy HH:mm',
            'yyyy-MM-dd HH:mm',
            'MM/dd/yy HH:mm',
            'dd-MM-yy HH:mm',
            'dd MMM yy HH:mm',
            'dd MMMM yyyy HH:mm',
        ];
        $formats = \array_combine(\array_keys(User::dateFormatList()), $values);
        return $formats[$format];
    }
}

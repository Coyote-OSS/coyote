<?php

namespace Coyote\Http\Controllers;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Illuminate\Http\Request;

class MailgunController extends Controller
{
    /**
     * @param UserRepository $user
     * @param Request $request
     */
    public function permanentFailure(UserRepository $user, Request $request)
    {
        abort_unless(
            $this->verify(
                $request->input('signature.timestamp'), 
                $request->input('signature.token'), 
                $request->input('signature.signature')),
            500
        );

        /** @var \Coyote\User $result */
        $result = $user->findBy('email', $request->input('event-data.recipient'), ['id', 'name', 'email', 'is_confirm']);

        if ($result && $result->is_confirm) {
            $result->is_confirm = false;
            $result->save();
        }

        logger()->info('Deactivate ' . $request->input('event-data.recipient') . ' due to permanent failure.');
    }

    /**
     * @param string $timestamp
     * @param string $token
     * @param string $signature
     * @return bool
     */
    private function verify(string $timestamp, string $token, string $signature): bool
    {
        return $signature === hash_hmac('sha256', $timestamp . $token, config('services.mailgun.secret'));
    }
}

<?php
namespace Coyote\Http\Controllers\User;

use Coyote\Services\UserVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class VerificationController extends BaseController {
    public function index(): View {
        $this->breadcrumb->push('Weryfikacja użytkownika', route('user.verification'));
        return $this->view('user.verification', [
            'userVerified' => $this->isVerified(),
        ]);
    }

    public function initiateVerification(UserVerificationService $verification): JsonResponse {
        if (!auth()->check()) {
            abort(401);
        }
        return response()->json([
            'initiateResult' => $verification->initiateVerification($this->requestPhoneNumber()),
        ]);
    }

    public function verify(UserVerificationService $twilioVerification): JsonResponse {
        if (!auth()->check()) {
            abort(401);
        }
        $result = $twilioVerification->verify(
            $this->requestPhoneNumber(),
            request()->get('verificationCode'));
        return response()->json([
            'verificationResult' => $result,
        ]);
    }

    private function requestPhoneNumber(): string {
        return '+48' . request()->get('phoneNumber');
    }

    private function isVerified(): bool {
        return auth()->user()->is_verified;
    }
}

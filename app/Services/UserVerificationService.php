<?php
namespace Coyote\Services;

use Coyote\Services\Twilio\InitiateResult;
use Coyote\Services\Twilio\TwilioClient;
use Coyote\Services\Twilio\VerificationResult;
use Coyote\User;

readonly class UserVerificationService {
    private TwilioClient $twilio;

    public function __construct() {
        $this->twilio = new TwilioClient(
            env('TWILIO_ACCOUNT_SID', docker_secret('TWILIO_ACCOUNT_SID_FILE')),
            env('TWILIO_AUTH_TOKEN', docker_secret('TWILIO_AUTH_TOKEN_FILE')),
            env('TWILIO_VERIFICATION_ID', docker_secret('TWILIO_VERIFICATION_ID_FILE')));
    }

    public function initiateVerification(string $phoneNumber): InitiateResult {
        return $this->twilio->initiateVerification($phoneNumber);
    }

    public function verify(string $phoneNumber, string $verificationCode): VerificationResult {
        $result = $this->twilio->verify($phoneNumber, $verificationCode);
        if ($result === VerificationResult::VERIFIED) {
            $this->verifyUser();
        }
        return $result;
    }

    private function verifyUser(): void {
        /** @var User $user */
        $user = auth()->user();
        $user->is_verified = true;
        $user->save();
    }
}

<?php
namespace Coyote\Services\Twilio;

use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

readonly class TwilioClient {
    private Client $twilio;

    public function __construct(
        string         $twilioAccountSid,
        string         $twilioAuthToken,
        private string $twilioVerificationId,
    ) {
        $this->twilio = new Client($twilioAccountSid, $twilioAuthToken);
    }

    public function initiateVerification(string $phoneNumber): InitiateResult {
        try {
            $this->twilio->verify->v2
                ->services($this->twilioVerificationId)
                ->verifications
                ->create($phoneNumber, 'sms')
                ->toArray();
            return InitiateResult::INITIATED;
        } catch (RestException) {
            return InitiateResult::REJECTED;
        } catch (ConfigurationException) {
            return InitiateResult::INTEGRATION_FAILURE;
        }
    }

    public function verify(string $phoneNumber, string $verificationCode): VerificationResult {
        try {
            $resultPayload = $this->twilio->verify->v2
                ->services($this->twilioVerificationId)
                ->verificationChecks
                ->create([
                    'to'   => $phoneNumber,
                    'code' => $verificationCode,
                ]);
        } catch (RestException|ConfigurationException) {
            // for user exceeding quota, rest exception is thrown
            // for invalid twilio credentials, configuration exception is thrown
            return VerificationResult::NOT_VERIFIED;
        }
        if ($resultPayload->status === 'approved') {
            return VerificationResult::VERIFIED;
        }
        return VerificationResult::NOT_VERIFIED;
    }
}

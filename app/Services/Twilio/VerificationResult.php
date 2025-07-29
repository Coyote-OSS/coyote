<?php
namespace Coyote\Services\Twilio;

enum VerificationResult: string {
    case VERIFIED = 'verified';
    case NOT_VERIFIED = 'notVerified';
}

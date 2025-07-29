<?php
namespace Coyote\Services\Twilio;

enum InitiateResult: string {
    case INITIATED = 'initiated';
    case REJECTED = 'rejected';
    case INTEGRATION_FAILURE = 'integrationFailure';
}

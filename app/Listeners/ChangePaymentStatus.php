<?php

namespace Coyote\Listeners;

use Coyote\Events\PaymentPaid;
use Coyote\Payment;

// DO NOT use ShouldQueue. We need to change status immediately.
class ChangePaymentStatus
{
    /**
     * Handle the event.
     *
     * @param  PaymentPaid  $event
     * @return void
     */
    public function handle(PaymentPaid $event)
    {
        $event->payment->status_id = Payment::PENDING;
        $event->payment->save();
    }
}

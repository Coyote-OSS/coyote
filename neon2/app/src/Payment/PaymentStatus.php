<?php
namespace Neon2\Payment;

enum PaymentStatus {
    case Completed;
    case Failed;
    case Awaiting;
}

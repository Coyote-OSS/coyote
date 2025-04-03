<?php
namespace Neon2;

enum JobOfferStatus: string
{
    case Published = 'published';
    case AwaitingPayment = 'awaitingPayment';
}

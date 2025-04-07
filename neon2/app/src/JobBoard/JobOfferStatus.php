<?php
namespace Neon2\JobBoard;

enum JobOfferStatus: string
{
    case Published = 'published';
    case AwaitingPayment = 'awaitingPayment';
}

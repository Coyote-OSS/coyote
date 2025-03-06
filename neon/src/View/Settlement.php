<?php
namespace Neon\View;

enum Settlement: string
{
    case Hourly = 'hourly';
    case Monthly = 'monthly';
    case Weekly = 'weekly';
    case Yearly = 'yearly';
}

<?php
namespace Neon2\JobBoard;

enum Rate: string {
    case Hourly = 'hourly';
    case Monthly = 'monthly';
    case Weekly = 'weekly';
    case Yearly = 'yearly';
}

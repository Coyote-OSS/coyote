<?php
namespace Coyote\Http\Middleware;

class PreventRequestForgery extends \Illuminate\Foundation\Http\Middleware\PreventRequestForgery {
    protected $except = [
        '/User/Settings/Ajax',
        '/Mikroblogi/Hit/*',
        '/Forum/Comment/*',
        '/Praca/Payment/Status',
        '/mailgun/permanent-failure',
        '/github/sponsorship',
        '/neon2/*',
    ];
}

<?php

namespace Coyote\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
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

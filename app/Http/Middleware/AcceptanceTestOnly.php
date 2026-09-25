<?php
namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Services\AcceptanceTest\AcceptanceTest;
use Illuminate\Http\Request;

readonly class AcceptanceTestOnly {
    public function __construct(private AcceptanceTest $acceptanceTest) {}

    public function handle(Request $request, Closure $next): mixed {
        if ($this->acceptanceTest->isTestMode()) {
            return $next($request);
        }
        abort(404);
    }
}

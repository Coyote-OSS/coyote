<?php
namespace Coyote\Http\Middleware;

use Closure;
use Coyote\Services\Guest;
use Coyote\Services\GuestEvent;
use Illuminate\Http\Request;

readonly class TrackCloudflareGeo {
    private const CAPTURED_HEADERS = [
        'cf-ipcity',
        'cf-ipcontinent',
        'cf-ipcountry',
        'cf-iplatitude',
        'cf-iplongitude',
        'cf-region',
        'cf-region-code',
        'cf-metro-code',
        'cf-postal-code',
        'cf-timezone',
    ];

    public function __construct(
        private Guest      $guest,
        private GuestEvent $event,
    ) {}

    public function handle(Request $request, Closure $next) {
        if (!$this->guest->getSetting('cfGeoAlreadyTracked', false)) {
            $headers = $this->cloudflareHeaders($request);
            if ($headers) {
                $this->event->event('cf-geo', $headers);
            }
            $this->guest->setSetting('cfGeoAlreadyTracked', true);
        }
        return $next($request);
    }

    private function cloudflareHeaders(Request $request): array {
        $headers = [];
        foreach (self::CAPTURED_HEADERS as $name) {
            $value = $request->header($name);
            if ($value !== null) {
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}

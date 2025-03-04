<?php
namespace Coyote\Http\Controllers\Firm;

use Coyote\Http\Controllers\Controller;
use Coyote\Services\Media\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmitController extends Controller
{
    public function logo(Request $request): JsonResponse
    {
        $this->validate($request, [
            'logo' => 'required|mimes:jpeg,jpg,png,gif',
        ]);
        $media = $this->getMediaFactory()->make('logo')->upload($request->file('logo'));
        return response()->json([
            'url' => url((string)$media->url()),
        ]);
    }

    private function getMediaFactory(): Factory
    {
        return app(\Coyote\Services\Media\Factory::class);
    }
}

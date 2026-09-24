<?php
namespace Coyote\Modules\JobBoard\User\Http;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\JobBoard\JobBoardStore;

class JobOffersController extends Controller {
    public function __construct(private readonly JobBoardStore $store) {}

    public function click(int $jobOfferId): Response {
        $this->store->clickJobOffer($jobOfferId);
        return response()->noContent();
    }
}

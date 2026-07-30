<?php
namespace Coyote\Modules\Campaigns\Adm\Http;

use Coyote\Http\Controllers\Adm\BaseController;
use Coyote\Services\Assets\UploadedFileStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Modules\Campaigns\Store\CampaignsStore;
use Modules\Campaigns\Store\VariantPayload;
use Modules\Campaigns\VariantType;

class VariantsController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->breadcrumb->push('Kampanie', route('adm.campaigns'));
        $this->breadcrumb->push('Warianty', route('adm.campaigns'));
    }

    public function upload(
        int                   $campaign,
        VariantsUploadRequest $request,
        CampaignsStore        $store,
        UploadedFileStorage   $storage,
    ): RedirectResponse {
        if ($store->findCampaign($campaign) === null) {
            abort(422);
        }
        $created = 0;
        $skipped = [];
        foreach ($request->file('images') as $file) {
            $reason = $this->createVariantFromFile($campaign, $file, $store, $storage);
            if ($reason === null) {
                $created++;
            } else {
                $skipped[] = $reason;
            }
        }
        $response = redirect()->route('adm.campaigns.show', [$campaign]);
        if ($created > 0) {
            $response->with('success', "$created warianty(-ów) zostały dodane.");
        }
        if ($skipped !== []) {
            $response->with('warning', 'Pominięto: ' . \implode('; ', $skipped));
        }
        return $response;
    }

    /**
     * @return string|null a skip reason, or null when the variant was created successfully
     */
    private function createVariantFromFile(
        int                 $campaign,
        UploadedFile        $file,
        CampaignsStore      $store,
        UploadedFileStorage $storage,
    ): ?string {
        $size = @\getImageSize($file->getRealPath());
        $filename = $file->getClientOriginalName();
        if ($size === false) {
            return "$filename (nie udało się odczytać obrazu)";
        }
        [$width, $height] = $size;
        $type = VariantType::fromSize($width, $height);
        if ($type === null) {
            return "$filename (nieobsługiwany rozmiar {$width}×{$height})";
        }
        $url = $storage->storeAndReturnUrl($file, $this->userId);
        if ($store->createVariant($campaign, new VariantPayload($type, $url)) === null) {
            return "$filename (nie udało się zapisać wariantu)";
        }
        return null;
    }
}

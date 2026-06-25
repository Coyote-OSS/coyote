<?php
namespace Modules\Campaigns\Store;

use Exception;
use Modules\Campaigns\VariantType;

readonly class VariantPayload {
    /**
     * @deprecated
     */
    public static function from(string $bannerType, string $imageUrl): self {
        return new VariantPayload(self::variantType($bannerType), $imageUrl);
    }

    private static function variantType(string $bannerType): VariantType {
        return match ($bannerType) {
            'horizontal'  => VariantType::Standard,
            'sidebar'     => VariantType::Sidebar,
            'leaderboard' => VariantType::LeaderBoard,
            default       => throw new Exception(),
        };
    }

    public function __construct(
        public VariantType $type,
        public string      $imageUrl,
    ) {}
}

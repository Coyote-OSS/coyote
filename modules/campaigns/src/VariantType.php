<?php
namespace Modules\Campaigns;

enum VariantType {
    case Banner;
    case Rectangle;
    case LeaderBoard;
    case BannerXl;
    case RectangleXl;
    case LeaderBoardXl;

    public static function fromSize(int $width, int $height): ?VariantType {
        return array_find(
            VariantType::cases(),
            fn($type) => self::widthHeight($type) === [$width, $height]);
    }

    private static function widthHeight(VariantType $type): array {
        return match ($type) {
            VariantType::Banner        => [728, 90],
            VariantType::Rectangle     => [300, 250],
            VariantType::LeaderBoard   => [1140, 90],
            VariantType::BannerXl      => [728, 200],
            VariantType::RectangleXl   => [300, 600],
            VariantType::LeaderBoardXl => [1140, 200],
        };
    }
}

<?php
namespace Modules\Campaigns;

enum VariantType {
    case Standard;
    case Sidebar;
    case LeaderBoard;
    case StandardXl;
    case SidebarXl;
    case LeaderBoardXl;

    public static function fromSize(int $width, int $height): ?VariantType {
        return array_find(
            VariantType::cases(),
            fn($type) => self::widthHeight($type) === [$width, $height]);
    }

    private static function widthHeight(VariantType $type): array {
        return match ($type) {
            VariantType::Standard      => [728, 90],
            VariantType::Sidebar       => [300, 250],
            VariantType::LeaderBoard   => [1140, 90],
            VariantType::StandardXl    => [728, 200],
            VariantType::SidebarXl     => [300, 600],
            VariantType::LeaderBoardXl => [1140, 200],
        };
    }
}

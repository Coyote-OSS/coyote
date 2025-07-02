<?php
namespace Coyote\View;

use Coyote\Forum;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;

readonly class NavigationMenuPresenter {
    public function navigationMenu(NavigationMenu $menu): array {
        return [
            'sections'          => $this->sections(),
            'allCategoriesHref' => route('forum.categories'),
            'headerItems'       => $this->headerItems($menu),
            'footerItems'       => $this->footerItems($menu),
        ];
    }

    private function sections(): array {
        return [
            [
                'title' => 'Dyskusje o Pracy',
                'icon'  => 'navigationSectionDiscussion',
                'href'  => $this->categoryHref('Kariera'),
                'items' => [
                    $this->forum('Kariera w IT', 'Jak nie wypaść z rynku?', promoted:true),
                    $this->forum('Opinie o pracodawcach', 'Sprawdź, zanim aplikujesz', promoted:true),
                    $this->forum('Ogłoszenia drobne', 'Kup, sprzedaj, ogłoś się'),
                    $this->forum('Edukacja', 'Kursy, szkoły, certyfikaty'),
                    $this->forum('Szkolenia i konferencje', 'Gdzie warto bywać?'),
                    $this->forum('CV do oceny'),
                    $this->topic('Ile zarabiacie?', 'Porównaj swoje zarobki', promoted:true),
                    $this->topic('Zwolnienia w IT', 'Forum / Kariera', promoted:true),
                    $this->topic('Koniec Eldorado w IT', 'Forum / Kariera'),
                ],
            ],
            [
                'title' => 'Dyskusje Techniczne',
                'icon'  => 'navigationSectionDiscussionTechnical',
                'href'  => route('forum.categories'),
                'items' => [
                    $this->forum('Hardware i Software'),
                    $this->forum('AI - Sztuczna inteligencja', trending:true),
                    $this->forum('C/C++', promoted:true),
                    $this->forum('C# i .NET'),
                    $this->forum('Java'),
                    $this->forum('Bazy danych'),
                    $this->forum('Python'),
                    $this->forum('Webmastering'),
                    $this->forum('Delphi/Pascal'),
                    $this->forum('PHP'),
                    $this->forum('JavaScript'),
                    $this->forum('Algorytmy i struktury danych'),
                    $this->forum('Inżynieria oprogramowania'),
                    $this->forum('Inne języki programowania'),
                ],
            ],
            [
                'title' => 'Hydepark',
                'icon'  => 'navigationSectionCommunity',
                'items' => [
                    $this->topic('trochę humoru... :-)', 'Śmieszki z IT', promoted:true),
                    $this->topic('Programistyczne WTF jakie Was spotkały', 'Kod, który boli'),
                    $this->forum('Off-Topic'),
                    $this->forum('Flame'),
                    $this->forum('Nietuzinkowe tematy'),
                ],
            ],
        ];
    }

    private function headerItems(NavigationMenu $menu): array {
        return [
            ['title' => "$menu->activeDiscussions aktywnych dyskusji dzisiaj",
             'icon'  => 'navigationActiveDiscussions',
             'help'  => 'Wątki aktywne w ciągu ostatnich 24 godzin.'],
            ['title'  => "$menu->usersOnline online",
             'icon'   => 'navigationOnlineUsers',
             'online' => true,
             'help'   => 'Użytkownicy aktywni w ciągu ostatnich 60 sekund.'],
        ];
    }

    private function footerItems(NavigationMenu $menu): array {
        if (!$menu->includeUserFilters) {
            return [];
        }
        return [
            ['title' => 'Wszystkie', 'href' => route('forum.all')],
            ['title' => 'Obserwowane', 'href' => route('forum.subscribes')],
            ['title' => 'Biorę udział', 'href' => route('forum.mine'), 'help' => 'Wątki w których brałem udział.'],
            ['title' => 'Z moimi tagami', 'href' => route('forum.interesting'), 'help' => 'Wątki zawierające moje tagi.'],
        ];
    }

    private function forum(
        string  $title,
        ?string $subtitle = null,
        ?bool   $promoted = null,
        ?bool   $trending = null,
    ): array {
        $categorySlug = $this->categorySlug($title);
        return [
            'title'    => $title,
            'subtitle' => $subtitle,
            'promoted' => $promoted ?? false,
            'trending' => $trending ?? false,
            'href'     => $this->categoryHref($categorySlug),
            'count'    => $this->formatCount($this->categoryPosts($categorySlug)),
        ];
    }

    private function categorySlug(string $title): string {
        $categories = [
            'C/C++'                        => 'C_i_C++',
            'Embedded'                     => 'Embedded',
            'Delphi/Pascal'                => 'Delphi_Pascal',
            'C# i .NET'                    => 'C_i_.NET',
            'Python'                       => 'Python',
            'Mobilne'                      => 'Mobilne',
            'Java'                         => 'Java',
            'JavaScript'                   => 'JavaScript',
            'Gamedev'                      => 'Gamedev',
            'PHP'                          => 'PHP',
            'Webmastering'                 => 'Webmastering',
            'Inne języki programowania'    => 'Inne',
            'VBA'                          => 'VBA',
            'Go'                           => 'Go',
            'Rust'                         => 'Rust',
            'Społeczność'                  => 'Spolecznosc',
            'Off-Topic'                    => 'Off-Topic',
            'Flame'                        => 'Flame',
            'Ogłoszenia drobne'            => 'Ogłoszenia_drobne',
            'Algorytmy i struktury danych' => 'Algorytmy',
            'AI - Sztuczna inteligencja'   => 'AI',
            'Dev/ops'                      => 'Devops',
            'Inżynieria oprogramowania'    => 'Inzynieria_oprogramowania',
            'Hardware i Software'          => 'Hardware_Software',
            'Bazy danych'                  => 'Bazy_danych',
            'Nietuzinkowe tematy'          => 'Nietuzinkowe_tematy',
            'Oceny i recenzje'             => 'Oceny_i_recenzje',
            'Szkolenia i konferencje'      => 'Szkolenia_i_konferencje',
            'Kariera w IT'                 => 'Kariera',
            'Edukacja'                     => 'Edukacja',
            'Opinie o pracodawcach'        => 'Opinie_o_pracodawcach',
            'CV do oceny'                  => 'CV_do_oceny',
        ];
        return $categories[$title] ?? throw new \Exception("Failed to read category slug: $title");
    }

    private function topic(string $title, string $subtitle, ?bool $promoted = null): array {
        [$topicHref, $count] = $this->topicHref($title);
        return [
            'title'    => $title,
            'subtitle' => $subtitle,
            'promoted' => $promoted ?? false,
            'href'     => $topicHref,
            'count'    => $this->formatCount($count),
        ];
    }

    private function topicHref(string $topicTitle): array {
        $topics = [
            'Ile zarabiacie?'                        => $this->topicUrl(233131),
            'Zwolnienia w IT'                        => $this->topicUrl(364478),
            'Koniec Eldorado w IT'                   => $this->topicUrl(372593),
            'trochę humoru... :-)'                   => $this->topicUrl(44030),
            'Programistyczne WTF jakie Was spotkały' => $this->topicUrl(141606),
        ];
        return $topics[$topicTitle];
    }

    private function topicUrl(int $topicId): ?array {
        $topic = Topic::query()->find($topicId);
        if ($topic === null) {
            return ['/', 0];
        }
        return [
            UrlBuilder::post($topic->lastPost),
            $topic->replies,
        ];
    }

    private function categoryPosts(string $categorySlug): int {
        return Forum::query()->where('slug', $categorySlug)->firstOrFail()->posts;
    }

    private function formatCount(int $postsCount): array {
        return [
            'long'  => "$postsCount postów",
            'short' => new FormatNumber()->format($postsCount),
        ];
    }

    private function categoryHref(string $categorySlug): string {
        return route('forum.category', [$categorySlug]);
    }
}

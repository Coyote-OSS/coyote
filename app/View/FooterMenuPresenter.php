<?php
namespace Coyote\View;

use Coyote\Domain\Clock;

readonly class FooterMenuPresenter {

    public function __construct(private Clock $clock) {}

    public function footerMenu(): array {
        return [
            'contactUsMail'         => 'info@4programmers.net',
            'contactUsFacebookHref' => 'https://www.facebook.com/4programmers.net',
            'contactUsLinkedInHref' => 'https://www.linkedin.com/company/4programmers',
            'contactUsGithubHref'   => 'https://github.com/Coyote-OSS/coyote',
            'copyrightYear'         => $this->clock->year(),
            'executionTime'         => $this->clock->executionTime(),
            'sections'              => [
                [
                    'title' => '4programmers',
                    'items' => [
                        ['title' => 'Kontakt', 'href' => $this->external('/Kontakt')],
                        ['title' => 'Reklama', 'href' => $this->external('/Reklama')],
                        ['title' => 'Patronat', 'href' => 'https://wydarzenia.4programmers.net/'],
                        ['title' => 'Pomoc', 'href' => $this->external('/Pomoc')],
                        ['title' => 'Zgłoś błąd', 'href' => 'https://github.com/Coyote-OSS/coyote/issues/new'],
                        ['title' => 'Regulamin', 'href' => $this->external('/Regulamin')],
                        ['title' => 'Polityka prywatności', 'href' => $this->external('/Polityka_prywatno%C5%9Bci')],
                    ],
                ],
                [
                    'title' => 'Dla Pracodawców',
                    'items' => [
                        ['title' => 'Cennik', 'href' => route('neon.jobOffer.pricing')],
                        // ['title' => 'Umów się na rozmowę', 'href' => ''],
                        ['title' => 'Załóż darmowe konto', 'href' => route('register')],
                        // ['title' => 'Stwórz profil twojej firmy', 'href' => ''],
                    ],
                ],
                [
                    'title' => 'Forum dyskusyjne',
                    'items' => [
                        ['title' => 'Oceny i recenzje', 'href' => '/Forum/Oceny_i_recenzje'],
                        ['title' => 'Wasze Projekty', 'href' => '/Forum/Spolecznosc/Projekty'],
                        ['title' => 'Coyote', 'href' => '/Forum/Coyote'],
                        ['title' => 'Społeczność 4p', 'href' => '/Forum/Spolecznosc'],
                        ['title' => 'Perełki', 'href' => '/Forum/Spolecznosc/Perełki'],
                        ['title' => 'Mobile', 'href' => '/Forum/Mobilne'],
                        ['title' => 'VBA', 'href' => '/Forum/VBA'],
                        ['title' => 'Devops', 'href' => '/Forum/Devops'],
                        ['title' => 'Gamedev', 'href' => '/Forum/Gamedev'],
                        ['title' => 'Embedded', 'href' => '/Forum/Embedded'],
                        ['title' => 'Go', 'href' => '/Forum/Go'],
                        ['title' => 'Rust', 'href' => '/Forum/Rust'],
                    ],
                ],
                [
                    'title' => 'Opinie o Pracodawcach',
                    'items' => [
                        ['title' => 'Onwelo', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/316057-onwelo_opinie')],
                        ['title' => 'Velo Bank', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/374205-velo_bank_opinie')],
                        ['title' => 'Revolut', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/366630-co_myslicie_o_pracy_w_revolut')],
                        ['title' => 'Tesco Technology', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/296870-tesco_technology')],
                        ['title' => 'Splunk Kraków', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/366386-oddzial_splunk_w_krakowie')],
                        ['title' => 'Inpost', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/368825-inpost_java_software_engineer')],
                        ['title' => 'Allegro', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/246484-praca_w_allegro')],
                        ['title' => 'Netflix Warszawa', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/367723-netflix_warszawa_opinie')],
                        ['title' => 'Sii Kraków', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/343511-sii_krakow')],
                        ['title' => 'Atlassian Gdańsk', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/361743-atlassian_gdansk_opinie')],
                        ['title' => 'Appfire', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/367878-appfire_opinie_informacje')],
                        ['title' => 'Quatrics Kraków', 'href' => $this->external('/Forum/Opinie_o_pracodawcach/302804-qualtrics_krakow')],
                    ],
                ],
            ],
        ];
    }

    private function external(string $url): string {
        return 'https://4programmers.net' . $url;
    }
}

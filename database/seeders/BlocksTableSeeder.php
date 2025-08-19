<?php
namespace Database\Seeders;

use Coyote\Domain\Icon\Icons;
use Illuminate\Database\Seeder;

class BlocksTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->addFooterBlock();
        $this->addBlogSidebar();
        $this->addJobAds();
    }

    private function addFooterBlock(): void
    {
        $icons = new Icons();
        $contactUs = $icons->icon('footerContactUs');
        $promoteFacebook = $icons->icon('footerPromoteFacebook');

        $content = <<<EOF
<div class="row max-width">
    <div class="col-md-2 col-sm-4 footer-column">
        <ul>
            <li><h6><a title="Zobacz nasze oferty pracy" href="/Praca">Praca dla programistów</a></h6></li>
            <li><a href="/Praca/Technologia/javascript">Praca JavaScript</a></li>
            <li><a href="/Praca/Technologia/java">Praca Java</a></li>
            <li><a href="/Praca/Technologia/c%23">Praca C#</a></li>
            <li><a href="/Praca/Technologia/php">Praca PHP</a></li>
            <li><a href="/Praca/Technologia/python">Praca Python</a></li>
            <li><a href="/Praca/Technologia/c%2B%2B">Praca C++</a></li>
        </ul>
    </div>

    <div class="col-md-2 col-sm-4 footer-column">
        <ul>
            <li><h6><a title="Forum dla programistów" href="/Forum">Forum dyskusyjne</a></h6></li>
            <li><a title="Forum dla początkujących programistów" href="/Forum/Newbie">Newbie</a></li>
            <li><a title="Forum dla programistów Javy" href="/Forum/Java">Java</a></li>
            <li><a title="Programowanie w C oraz C++" href="/Forum/C_i_C++">C/C++</a></li>
            <li><a title="Programowanie w C oraz C++" href="/Forum/C_i_.NET">C# i .NET</a></li>
            <li><a title="Kariera programisty" href="/Forum/Kariera">Kariera</a></li>
            <li><a title="Praca dla programisty" href="/Forum/Edukacja">Edukacja</a></li>
        </ul>
    </div>

    <div class="col-md-2 col-sm-4 footer-column">
        <ul>
            <li><h6>Sprawy administracyjne</h6></li>
            <li><a title="Jeżeli znalazłeś błąd w oprogramowaniu, tu jest odpowiednie miejsce, aby to zgłosić" href="https://github.com/adam-boduch/coyote/issues">Zgłoś błąd w oprogramowaniu</a></li>
            <li><a title="4programmers.net na facebooku" href="https://www.facebook.com/4programmers.net">4programmers.net na facebooku</a></li>
            <li><a title="Blog 4programmers.net" href="/Blog">Blog 4programmers.net</a></li>
            <li><a title="Prawa autorskie" href="/Prawa_autorskie">Prawa autorskie</a></li>
            <li><a title="Logo 4programmers.net" href="/Pomoc/Nasze_logo">Nasze logo</a></li>
            <li><a title="Link do repozytorium na Github, dyskusja o 4programmers.net" href="/Forum/Coyote">Coyote</a></li>
        </ul>
    </div>

    <div class="col-md-2 col-sm-4 footer-column">
        <ul>
            <li><h6>O nas</h6></li>
            <li><a title="Kontakt z serwisem 4programmers.net" href="/Kontakt">Kontakt</a></li>
            <li><a title="Regulamin korzystania z serwisu 4programmers.net" href="/Regulamin">Regulamin</a></li>
            <li><a title="Polityka prywatności serwisu 4programmers.net" href="/Polityka_prywatności">Polityka prywatności</a></li>
            <li><a title="Zareklamuj się w serwisie 4programmers.net" href="/Reklama">Reklama</a></li>
            <li><a title="Pomoc w korzystaniu z serwisu 4programmers.net" href="/Pomoc">Pomoc</a></li>
            <li><a title="Odpowiedzi na najczęściej zadawane pytania" href="/Patronat">Patronat</a></li>
        </ul>
    </div>

    <div class="col-sm-4">
        <div class="footer-bubble neon-tile neon-tile--muted neon-rounded">
            <h6>Skontaktuj się z nami</h6>
            <ol>
                <li>
                    <a title="Skontaktuj się poprzez email" href="/Kontakt">
                        $contactUs
                        Napisz do nas
                    </a>
                </li>
                <li>
                    <a title="Skontaktuj się poprzez email" href="https://www.facebook.com/4programmers.net">
                        $promoteFacebook 
                        Odwiedź nas na Facebooku
                    </a>
                </li>
            </ol>
        </div>
    </div>
</div>
EOF;

        \Coyote\Block::query()->create([
            'name'    => 'footer',
            'region'  => 'footer',
            'content' => $content,
        ]);
    }

    private function addBlogSidebar(): void
    {
        $content = <<<EOF
<p>
    <strong>Błędy, uwagi ogólne</strong><br>
    <a href="mailto:support@4programmers.net">support@4programmers.net</a>
</p>

<p>
    <strong>Patronat nad wydarzeniami</strong><br>
    <a href="mailto:patronat@4programmers.net">patronat@4programmers.net</a>
</p>
EOF;

        \Coyote\Block::query()->create([
            'name'    => 'blog_sidebar',
            'content' => $content,
        ]);
    }

    private function addJobAds(): void
    {
        $content = <<<EOF
<div id="hire-me"></div> 
<script>
    var xhr= new XMLHttpRequest();
    xhr.open('GET', '/Praca/recommendations', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange= function() {
        if (this.readyState !== 4) return;
        if (this.status !== 200) return;
        const block = document.getElementById('hire-me');
        block.innerHTML = this.responseText;
        block.style.removeProperty('min-height');
    };
    xhr.send();
</script>
EOF;
        \Coyote\Block::query()->create([
            'name'    => 'job_ads',
            'region'  => null,
            'content' => $content,
        ]);
    }
}

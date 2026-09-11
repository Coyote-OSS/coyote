<?php

namespace Tests\Legacy\LaravelIntegration\Services\Parser\Parsers;

use Coyote\Page;
use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Coyote\Services\Parser\Parsers\Markdown;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Legacy\LaravelIntegration\TestCase;

class MarkdownTest extends TestCase
{
    use DatabaseTransactions;

    private Markdown $markdown;

    public function setUp(): void
    {
        parent::setUp();
        $this->markdown = new Markdown(
            $this->app[UserRepositoryInterface::class],
            $this->app[PageRepositoryInterface::class],
            '4programmers.net');
    }

    public function testParseUserName()
    {
        $input = $this->markdown->parse('@');
        $this->assertEquals('<p>@</p>', trim($input));

        $input = $this->markdown->parse('@admin lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>/', trim($input));

        $input = $this->markdown->parse('(@admin)');
        $this->assertMatchesRegularExpression('/\(<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>\)/', trim($input));

        $input = $this->markdown->parse("@admin\n(@admin)");
        $this->assertMatchesRegularExpression("/<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a><br>\n\(<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a>\)/", trim($input));

        $input = $this->markdown->parse('@admin lorem ipsum `@admin`');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> lorem ipsum <code>@admin<\/code>/', trim($input));

        $input = $this->markdown->parse('@admin: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>: lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin:lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>:lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin, lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>, lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin. lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>. lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin\'s. lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a>\'s. lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin @admin');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> <a class="mention" data-user-id="\d+" href=".*">@admin<\/a>/', trim($input));

        $input = $this->markdown->parse("@admin\n@admin");
        $this->assertMatchesRegularExpression("/<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a><br>\n<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a>/", trim($input));

        $input = $this->markdown->parse("@admin… foo");
        $this->assertMatchesRegularExpression("/<a class=\"mention\" data-user-id=\"\d+\" href=\".*\">@admin<\/a>… foo/", $input);

        $now = new \DateTime('now');
        factory(User::class)->create(['name' => 'admin admiński', 'email' => 'foo@bar.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@{admin} lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{admin admiński} lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin admiński<\/a> lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{admin admiński}lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin admiński<\/a>lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{admin admiński}:lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin admiński<\/a>:lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin admiński: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@admin<\/a> admiński: lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{admin-admiński} lorem ipsum');
        $this->assertMatchesRegularExpression('/@admin-admiński lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@admin-admiński lorem ipsum');
        $this->assertMatchesRegularExpression('/@admin-admiński lorem ipsum/', trim($input));

        factory(User::class)->create(['name' => 'p88.yyy', 'email' => 'foo@bar.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@p88.yyy lorem ipsum');
        $this->assertMatchesRegularExpression('/@p88.yyy lorem ipsum/', trim($input));

        $input = $this->markdown->parse('@{p88.yyy}: lorem ipsum');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@p88.yyy<\/a>: lorem ipsum/', trim($input));

        factory(User::class)->create(['name' => 'somedomain', 'email' => 'support@somedomain.net', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('support@somedomain.net');
        $this->assertMatchesRegularExpression('/support@somedomain.net/', trim($input));

        $input = $this->markdown->parse('(@somedomain) lorem ipsum');
        $this->assertMatchesRegularExpression('/\(<a class="mention" data-user-id="\d+" href=".*">@somedomain<\/a>\) lorem ipsum/', trim($input));

        factory(User::class)->create(['name' => 'First(Name)', 'email' => 'bruno@m.com', 'created_at' => $now, 'updated_at' => $now]);

        $input = $this->markdown->parse('@{First(Name)}: hello');
        $this->assertMatchesRegularExpression('/<a class="mention" data-user-id="\d+" href=".*">@First\(Name\)<\/a>: hello/', trim($input));

        $input = $this->markdown->parse('@ 2Ghz');
        $this->assertStringContainsString('@ 2Ghz', trim($input));
    }

    public function testParseLinks()
    {
        $input = '<a href="http://www.google.pl/">http://www.google.pl/</a>';
        $this->assertEquals("<p>$input</p>", trim($this->markdown->parse($input)));

        $input = '<a href="http://www.google.pl/">http://www.google.pl/</a>';
        $this->assertEquals("<p>$input</p>", trim($this->markdown->parse($input)));

        $input = $this->markdown->parse('[http://www.google.pl/](http://www.google.pl/)');
        $this->assertEquals('<p><a href="http://www.google.pl/">http://www.google.pl/</a></p>', trim($input));

        $input = $this->markdown->parse('[test](http://www.google.pl/)');
        $this->assertEquals('<p><a href="http://www.google.pl/">test</a></p>', trim($input));

        $input = $this->markdown->parse('http://4programmers.net');
        $this->assertEquals('<p><a href="http://4programmers.net">http://4programmers.net</a></p>', trim($input));

        $input = $this->markdown->parse('to: http://4programmers.net.');
        $this->assertEquals('<p>to: <a href="http://4programmers.net">http://4programmers.net</a>.</p>', trim($input));

        $input = $this->markdown->parse('<http://4programmers.net>');
        $this->assertEquals('<p><a href="http://4programmers.net">http://4programmers.net</a></p>', trim($input));

        $input = $this->markdown->parse('<a href="http://4programmers.net">http://4programmers.net</a>');
        $this->assertEquals('<p><a href="http://4programmers.net">http://4programmers.net</a></p>', trim($input));

        $input = $this->markdown->parse('www.4programmers.net');
        $this->assertEquals('<p><a href="http://www.4programmers.net">www.4programmers.net</a></p>', trim($input));

        $input = $this->markdown->parse('foo@bar.com');
        $this->assertEquals('<p><a href="mailto:foo@bar.com">foo@bar.com</a></p>', trim($input));

        $input = $this->markdown->parse('<foo@bar.com>');
        $this->assertEquals('<p><a href="mailto:foo@bar.com">foo@bar.com</a></p>', trim($input));

        $input = '<a href="http://4programmers.net">4programmers</a>.net';
        $this->assertEquals("<p>$input</p>", trim($this->markdown->parse($input)));

        $input = 'www.4programmers.net';
        $this->assertEquals('<p><a href="http://www.4programmers.net">www.4programmers.net</a></p>', trim($this->markdown->parse($input)));

        $input = 'asp.net';
        $this->assertEquals('<p>asp.net</p>', trim($this->markdown->parse($input)));

        $link = 'http://pl.wikipedia.org/wiki/normalna_(bazy_danych)';
        $this->assertEquals("<p><a href=\"$link\">$link</a></p>", trim($this->markdown->parse($link)));

        $link = 'https://docs.djangoproject.com/en/2.0/#first-steps';
        $this->assertEquals("<p><a href=\"$link\">$link</a></p>", trim($this->markdown->parse($link)));

        $link = '[**foo**](https://foo.me/)';
        $this->assertEquals("<p><a href=\"https://foo.me/\"><strong>foo</strong></a></p>", trim($this->markdown->parse($link)));
    }

    public function testYoutubeVideos()
    {
        $this->assertStringContainsString('iframe', $this->markdown->parse('https://www.youtube.com/watch?v=7dU3ybPqV94'));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse(link_to('https://www.youtube.com/watch?v=7dU3ybPqV94')));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse(link_to('https://www.youtube.com/watch?v=7dU3ybPqV94#foo')));
        $this->assertStringContainsString('iframe', $this->markdown->parse('https://youtu.be/enOjqwOE1ec'));
        $this->assertStringContainsString('iframe', $this->markdown->parse('https://www.youtu.be/enOjqwOE1ec'));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse('https://youtu.be/'));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse('https://youtube.com'));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse('https://www.youtube.com'));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse(link_to('https://youtu.be/enOjqwOE1ec')));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse('[test](https://youtu.be/enOjqwOE1ec)'));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse('<i>https://youtu.be/enOjqwOE1ec</i>'));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse('*https://youtu.be/enOjqwOE1ec*'));

        $this->assertEquals('<p><a href="https://www.youtube.com/watch?v=SC9ybxMDGlE">test</a></p>', trim($this->markdown->parse('<a href="https://www.youtube.com/watch?v=SC9ybxMDGlE">test</a>')));
        $this->assertStringNotContainsString('iframe', $this->markdown->parse('<a href="https://www.youtube.com/watch?v=SC9ybxMDGlE">https://www.youtube.com/watch?v=SC9ybxMDGlE</a>'));

        $this->assertStringContainsString('https://www.youtube.com/watch?v=7dU3ybPqV94', $this->markdown->parse('<code>https://www.youtube.com/watch?v=7dU3ybPqV94</code>'));

        $this->assertStringContainsString('https://youtube.com/embed/vd0zDG4vwOw?start=1107', $this->markdown->parse('https://youtu.be/vd0zDG4vwOw?t=18m27s'));
        $this->assertStringContainsString('https://youtube.com/embed/vd0zDG4vwOw?start=1107', $this->markdown->parse('https://www.youtube.com/watch?v=vd0zDG4vwOw#t=18m27s'));
    }

    public function testParseInternalLinks()
    {
        $title = '"Kompetentność" uczących się programowania';
        $path = '/Forum/Spolecznosc/266098-kompetentnosc_uczacych_sie_programowania';
        $this->createPage($title, $path);

        $input = 'http://4programmers.net' . $path;

        $this->assertEquals(
            '<p><a href="http://4programmers.net' . $path . '" title="' . htmlspecialchars($title) . '">' . htmlspecialchars($title) . '</a></p>',
            trim($this->markdown->parse($input))
        );

        $title = 'łatwo przyszło, łatwo poszło';
        $path = '/Forum/Spolecznosc/' . str_slug($title);
        $this->createPage($title, $path);

        $input = 'http://4programmers.net' . $path;

        $this->assertEquals(
            '<p><a href="http://4programmers.net' . $path . '" title="' . $title . '">' . $title . '</a></p>',
            trim($this->markdown->parse($input))
        );

        $input = '[customowy tytuł](http://4programmers.net' . $path . ')';

        $this->assertEquals(
            '<p><a href="http://4programmers.net' . $path . '">customowy tytuł</a></p>',
            trim($this->markdown->parse($input))
        );
    }

    public function testParseInternalAccessors()
    {
        $input = $this->markdown->parse('[[Pomoc/Konto/Czy muszę utworzyć konto?]]');
        $this->assertMatchesRegularExpression("~<a class=\"link-broken\" href=\"Create/Pomoc/Konto/Czy_muszę_utworzyć_konto\" title=\"Dokument nie istnieje\">Czy muszę utworzyć konto?</a>~", $input);

        $input = $this->markdown->parse('[[Pomoc/Konto/Czy_muszę_utworzyć_konto?]]');
        $this->assertMatchesRegularExpression("~<a class=\"link-broken\" href=\"Create/Pomoc/Konto/Czy_muszę_utworzyć_konto\" title=\"Dokument nie istnieje\">Czy muszę utworzyć konto?</a>~", $input);

        $title = 'Forum dyskusyjne';
        $path = '/Discussion_board';

        $this->createPage($title, $path);

        $input = $this->markdown->parse('[[Discussion board]]');
        $this->assertMatchesRegularExpression("~<a href=\"" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->markdown->parse('[[Discussion_board]]');
        $this->assertMatchesRegularExpression("~<a href=\"" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->markdown->parse('[[Discussion board|takie tam forum]]');
        $this->assertMatchesRegularExpression("~<a href=\"" . preg_quote($path) . "\">takie tam forum</a>~", $input);

        $input = $this->markdown->parse('[[Discussion board#section|takie tam forum]]');
        $this->assertMatchesRegularExpression("~<a href=\"" . preg_quote($path) . "#section\">takie tam forum</a>~", $input);

        $input = $this->markdown->parse('[[Discussion board#section]]');
        $this->assertMatchesRegularExpression("~<a href=\"" . preg_quote($path) . "#section\">$title</a>~", $input);

        $title = 'Newbie';
        $path = '/Discussion_board/Newbie';

        $this->createPage($title, $path);

        $input = $this->markdown->parse('[[Discussion board/Newbie]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">$title</a>~", $input);

        $input = $this->markdown->parse('[[Discussion board/Newbie|forum newbie]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">forum newbie</a>~", $input);

        $title = 'Kim jesteśmy?';
        $path = '/Kim_jesteśmy';

        $this->createPage($title, $path);

        $input = $this->markdown->parse('[[Kim jesteśmy?]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">" . preg_quote($title) . "</a>~", $input);

        $input = $this->markdown->parse('[[Foo Bar]]');
        $this->assertMatchesRegularExpression('~<a class="link-broken" href="Create/Foo_Bar" title="Dokument nie istnieje">Foo Bar</a>~', $input);

        $path = '/Pomoc/Konto/Czy_muszę_utworzyć_konto';
        $title = 'Czy muszę utworzyć konto?';

        $this->createPage($title, $path);

        $input = $this->markdown->parse('[[Pomoc/Konto/Czy muszę utworzyć konto?]]');
        $this->assertMatchesRegularExpression("~<a href=\".*" . preg_quote($path) . "\">" . preg_quote($title) . "</a>~", $input);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function inlineEmoji()
    {
        $this->markdown->parse('*:emoji:*');
    }

    private function createPage($title, $path)
    {
        $now = new \DateTime('now');
        Page::forceCreate([
            'title' => $title, 'path' => $path, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}

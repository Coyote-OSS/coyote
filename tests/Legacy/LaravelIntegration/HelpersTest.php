<?php

namespace Tests\Legacy\LaravelIntegration;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    /**
     * @test
     */
    public function testExcerpt()
    {
        $this->assertEquals('Praca w oparciu o "kontrakt" (czyli umowa B2B)', excerpt('Praca w oparciu o "kontrakt" (czyli umowa B2B)'));
        $this->assertEquals('<xxx>', excerpt('&lt;xxx&gt;'));
    }

    /**
     * @test
     */
    public function testPlain()
    {
        $this->assertEquals('"test"', plain('<b>"test"</b>'));
        $this->assertEquals("'test'", plain("<b>'test'</b>"));
        $this->assertEquals("a > b", plain("a > b"));
        $this->assertEquals('<xxx>', plain('&lt;xxx&gt;'));
        $this->assertEquals('&lt;xxx&gt;', plain('&amp;lt;xxx&amp;gt;'));
    }

    /**
     * @test
     */
    public function shouldGetKeywords()
    {
        // given
        $text = 'foo bar i18n bar foo i18n www.google.com ipsum i18n';

        // when
        $keywords = keywords($text, 4);

        // then
        $this->assertEquals(['i18n', 'foo', 'bar', 'wwwgooglecom'], $keywords);
    }

    /**
     * @test
     */
    public function shouldGetKeywords_forLimit0()
    {
        // given
        $text = 'foo bar i18n bar foo i18n www.google.com ipsum i18n';

        // when
        $keywords = keywords($text, 0);

        // then
        $this->assertEquals(['i18n', 'foo', 'bar', 'wwwgooglecom', 'ipsum'], $keywords);
    }

    /**
     * @test
     */
    public function shouldGetKeywords_forNegative()
    {
        // given
        $text = 'foo bar i18n bar foo i18n www.google.com ipsum i18n';

        // when
        $keywords = keywords($text, -4); // TODO InvalidArgumentException ?

        // then
        $this->assertEquals(['i18n', /*'foo', 'bar', 'wwwgooglecom', 'ipsum'*/], $keywords);
    }
}

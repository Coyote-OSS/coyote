<?php
namespace Tests\Integration\ColorScheme;

use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture;
use Tests\Integration\ColorScheme;

class ColorSchemeTest extends TestCase
{
    use BaseFixture\Server\Laravel\Transactional;
    use ColorScheme\Fixture\ColorScheme;

    /**
     * @test
     */
    public function defaultColorScheme()
    {
        $this->assertSame('system', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeLight()
    {
        $this->setColorScheme('light');
        $this->assertSame('light', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeDark()
    {
        $this->setColorScheme('dark');
        $this->assertSame('dark', $this->colorScheme());
    }

    /**
     * @test
     */
    public function colorSchemeSystem()
    {
        $this->setColorScheme('system');
        $this->assertSame('system', $this->colorScheme());
    }
}

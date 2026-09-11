<?php
namespace Tests\Legacy\Unit\Initials;

use Coyote\Domain\Initials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InitialsTokensTest extends TestCase
{
    #[Test]
    public function test(): void
    {
        $this->assertTokens('admin', ['admin']);
    }

    #[Test]
    public function accent(): void
    {
        $this->assertTokens('śliwa', ['śliwa']);
    }

    #[Test]
    public function firstAndLastName(): void
    {
        $this->assertTokens('Mark Twain', ['Mark', 'Twain']);
    }

    #[Test]
    public function firstAndLastNameAccent(): void
    {
        $this->assertTokens('Ędward Ącki', ['Ędward', 'Ącki']);
    }

    #[Test]
    public function CamelCase(): void
    {
        $this->assertTokens('JohnRambo', ['Joh', 'Rambo']);
    }

    #[Test]
    public function CamelCaseAccent(): void
    {
        $this->assertTokens('ĘdwardĄcki', ['Ędwar', 'Ącki']);
        $this->assertTokens('MichałAdam', ['Micha', 'Adam']);
    }

    #[Test]
    public function firstAndLastNamePeriod(): void
    {
        $this->assertTokens('mark.twain', ['mark', 'twain']);
        $this->assertTokens('.mark.twain.', ['mark', 'twain']);
    }

    #[Test]
    public function separatedByNonLetter(): void
    {
        $this->assertTokens('mark|twain', ['mark', 'twain']);
    }

    #[Test]
    public function ignoreSpecialCharacters(): void
    {
        $this->assertTokens('!foo@bar', ['foo', 'bar']);
    }

    #[Test]
    public function nonLetters(): void
    {
        $this->assertTokens('!@#$%^[]', []);
    }

    #[Test]
    public function productionUsers(): void
    {
        $this->assertTokens('Rav_sql', ['Rav', 'sql']);
        $this->assertTokens('Michał 1', ['Michał', '1']);
        $this->assertTokens('MarekR22', ['Mare', 'R22']);
    }

    #[Test]
    public function wordsWithDigits(): void
    {
        $this->assertTokens('Mark1 Twain2', ['Mark1', 'Twain2']);
    }

    #[Test]
    public function wordWithDigit(): void
    {
        $this->assertTokens('Mark1', ['Mark', '1']);
    }

    #[Test]
    public function uppercase(): void
    {
        $this->assertTokens('FOO', ['FOO']);
    }

    #[Test]
    public function twoWordsWithDigit(): void
    {
        $this->assertTokens('Marek', ['Marek']);
        $this->assertTokens('Mar3k', ['Mar', '3k']);
        $this->assertTokens('Mar3k Foo', ['Mar3k', 'Foo']);
    }

    private function assertTokens(string $username, array $expectedTokens): void
    {
        $initials = new Initials();
        $this->assertSame($expectedTokens, $initials->usernameWords($username));
    }
}

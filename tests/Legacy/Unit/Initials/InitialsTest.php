<?php
namespace Tests\Legacy\Unit\Initials;

use Coyote\Domain\Initials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InitialsTest extends TestCase
{
    #[Test]
    public function test(): void
    {
        $this->assertInitials('admin', 'AD');
    }

    #[Test]
    public function accent(): void
    {
        $this->assertInitials('śliwa', 'ŚL');
    }

    #[Test]
    public function firstAndLastName(): void
    {
        $this->assertInitials('Mark Twain', 'MT');
    }

    #[Test]
    public function firstAndLastNameAccent(): void
    {
        $this->assertInitials('Ędward Ącki', 'ĘĄ');
    }

    #[Test]
    public function CamelCase(): void
    {
        $this->assertInitials('JohnRambo', 'JR');
    }

    #[Test]
    public function CamelCaseAccent(): void
    {
        $this->assertInitials('ĘdwardĄcki', 'ĘĄ');
        $this->assertInitials('MichałAdam', 'MA');
    }

    #[Test]
    public function firstAndLastNamePeriod(): void
    {
        $this->assertInitials('mark.twain', 'MT');
        $this->assertInitials('.mark.twain.', 'MT');
    }

    #[Test]
    public function separatedByNonLetter(): void
    {
        $this->assertInitials('mark|twain', 'MT');
    }

    #[Test]
    public function ignoreSpecialCharacters(): void
    {
        $this->assertInitials('!foo@bar', 'FB');
    }

    #[Test]
    public function nonLetters(): void
    {
        $this->assertInitials('!@#$%^[]', '4p');
    }

    #[Test]
    public function short(): void
    {
        $this->assertInitials('s', '4p');
    }

    #[Test]
    public function wordsWithDigit(): void
    {
        $this->assertInitials('FooBar100', 'FB');
        $this->assertInitials('FOOBAR100', 'F1');
    }

    #[Test]
    public function productionUsers(): void
    {
        $this->assertInitials('Rav_sql', 'RS');
        $this->assertInitials('Michał 1', 'M1');
        $this->assertInitials('xpietrzak721', 'X7');
    }

    private function assertInitials(string $username, string $expectedInitials): void
    {
        $initials = new Initials();
        $this->assertSame($expectedInitials, $initials->of($username));
    }

    #[Test]
    public function letterDigit(): void
    {
        $this->assertInitials('A9', 'A9');
    }

    #[Test]
    public function leadingDot(): void
    {
        $this->assertInitials('.FooOfBar.', 'FO');
    }

    #[Test]
    public function singleLetterBeforeCamelCase(): void
    {
        $this->assertInitials('wElcome', 'WE');
    }
}

<?php
namespace Tests\Legacy\IntegrationNew\TempEmail;

use Coyote\Domain\TempEmail\TempEmailCategory;
use Coyote\Domain\TempEmail\TempEmailRepository;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture;

class TempEmailRepositoryTest extends TestCase {
    use BaseFixture\Server\Laravel\Application;

    private TempEmailRepository $tempEmails;

    #[Before]
    public function initialize(): void {
        $this->tempEmails = new TempEmailRepository();
    }

    #[Test]
    public function gmailIsTrusted(): void {
        $this->assertSame(
            TempEmailCategory::TRUSTED,
            $this->tempEmails->findCategory('user@gmail.com'));
    }

    #[Test]
    public function onetIsTrusted(): void {
        $this->assertSame(
            TempEmailCategory::TRUSTED,
            $this->tempEmails->findCategory('user@onet.pl'));
    }

    #[Test]
    public function wpIsTrusted(): void {
        $this->assertSame(
            TempEmailCategory::TRUSTED,
            $this->tempEmails->findCategory('user@wp.pl'));
    }

    #[Test]
    public function niepodamIsTemporary(): void {
        $this->assertSame(
            TempEmailCategory::TEMPORARY,
            $this->tempEmails->findCategory('user@niepodam.pl'));
    }

    #[Test]
    public function xfavajIsTemporary(): void {
        $this->assertSame(
            TempEmailCategory::TEMPORARY,
            $this->tempEmails->findCategory('user@xfavaj.com'));
    }

    #[Test]
    public function unknownEmailDomainIsUnknown(): void {
        $emailNotTrustedAndAlsoNotTemporary = 'user@4programmers.net';
        $this->assertSame(
            TempEmailCategory::UNKNOWN,
            $this->tempEmails->findCategory($emailNotTrustedAndAlsoNotTemporary));
    }
}

<?php
namespace Tests\Integration\Modules\Campaigns\Provided;

use Coyote\Modules\Campaigns\Provided\CloudflareVoivodeship;
use Illuminate\Http\Request;
use Modules\Campaigns\Voivodeship;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture;

#[CoversClass(CloudflareVoivodeship::class)]
class CloudflareVoivodeshipTest extends TestCase {
    use BaseFixture\Server\Laravel\Application;

    #[Test]
    public function requestWithoutCloudflareHeaders_hasNoVoivodeship(): void {
        $this->request([]);
        $this->assertNull($this->voivodeship());
    }

    #[Test]
    public function polishRequestWithRegion_hasVoivodeship(): void {
        $this->request(['HTTP_CF_IPCOUNTRY' => 'PL', 'HTTP_CF_REGION' => 'Mazovia']);
        $this->assertSame(Voivodeship::Mazowieckie, $this->voivodeship());
    }

    #[Test]
    public function polishRequestWithoutRegion_hasNoVoivodeship(): void {
        $this->request(['HTTP_CF_IPCOUNTRY' => 'PL']);
        $this->assertNull($this->voivodeship());
    }

    #[Test]
    public function foreignRequestWithRegion_hasNoVoivodeship(): void {
        $this->request(['HTTP_CF_IPCOUNTRY' => 'DE', 'HTTP_CF_REGION' => 'Mazovia']);
        $this->assertNull($this->voivodeship());
    }

    private function request(array $server): void {
        app()->instance('request', Request::create('/', server:$server));
    }

    private function voivodeship(): ?Voivodeship {
        return new CloudflareVoivodeship()->currentUserVoivodeship();
    }
}

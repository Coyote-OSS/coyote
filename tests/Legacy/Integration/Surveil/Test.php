<?php
namespace Tests\Legacy\Integration\Surveil;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Server;
use Tests\Legacy\Integration\Surveil;

class Test extends TestCase
{
    use Server\Http;
    use Surveil\Fixture\Models;

    /**
     * @before
     */
    public function removeSettings(): void
    {
        $this->query()->whereIn('key', ['setting.key', 'foo.bar'])->delete();
    }

    /**
     * @test
     */
    public function insertFirst(): void
    {
        $this->postRequest('setting.key');
        $this->assertSame('1', $this->settingValue('setting.key'));
    }

    /**
     * @test
     */
    public function noDuplicates(): void
    {
        $this->existingSetting('foo.bar', 'value');
        $this->postRequest('foo.bar');
        $this->assertSame(1, $this->countByKey('foo.bar'));
    }

    /**
     * @test
     */
    public function incrementSetting(): void
    {
        $this->existingSetting('foo.bar', '3');
        $this->postRequest('foo.bar');
        $this->assertSame('4', $this->settingValue('foo.bar'));
    }

    /**
     * @test
     */
    public function missingKey(): void
    {
        $this->postBodyData(['other' => 'key'])
            ->assertStatus(400);
    }

    private function postRequest(string $settingKey): void
    {
        $this->postBodyData(['key' => $settingKey])->assertSuccessful();
    }

    private function postBodyData(array $body): TestResponse
    {
        return $this->server->post('/Settings/Ajax', $body);
    }
}

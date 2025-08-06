<?php

namespace Tests\Legacy\IntegrationOld\Services;

use Coyote\Firewall;
use Coyote\Repositories\Contracts\FirewallRepositoryInterface;
use Coyote\Services\Rules;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Tests\Legacy\IntegrationOld\TestCase;

class RulesTest extends TestCase
{
    use WithFaker;

    public function testIpv4RuleShouldPass()
    {
        $ip = '127.0.0.1';
        $model = ['ip' => '127.0.*', 'user_id' => null, 'id' => $this->faker->numberBetween(0, 5000)];

        $cache = $this->partialMock(Repository::class, function (MockInterface $mock) use ($model) {
            $mock->shouldReceive('rememberForever')->andReturn(serialize([$model]));
        });

        $request = $this->partialMock(Request::class, function (MockInterface $mock) use ($ip) {
            $mock->shouldReceive('ip')->andReturn($ip);
        });

        $firewallRepository = $this->mock(FirewallRepositoryInterface::class, function (MockInterface $mock) use ($model) {
            return $mock->shouldReceive('find')->andReturn(new Firewall()->forceFill($model));
        });

        $rules = new Rules($cache, $firewallRepository);

        $this->assertInstanceOf(Firewall::class, $rules->find($request));
    }

    public function testIpv6RuleShouldPass()
    {
        $ip = '2405:8100:8000:5ca1::278:93c:ab1';
        $model = ['ip' => '2405:8100:8000:5ca1::278:93c:*', 'user_id' => null, 'id' => $this->faker->numberBetween(0, 5000)];

        $cache = $this->partialMock(Repository::class, function (MockInterface $mock) use ($model) {
            $mock->shouldReceive('rememberForever')->andReturn(serialize([$model]));
        });

        $request = $this->partialMock(Request::class, function (MockInterface $mock) use ($ip) {
            $mock->shouldReceive('ip')->andReturn($ip);
        });

        $firewallRepository = $this->mock(FirewallRepositoryInterface::class, function (MockInterface $mock) use ($model) {
            return $mock->shouldReceive('find')->andReturn(new Firewall()->forceFill($model));
        });

        $rules = new Rules($cache, $firewallRepository);

        $this->assertInstanceOf(Firewall::class, $rules->find($request));
    }

    public function testIpv6RuleShouldNotPass()
    {
        $ip = '2405:8100:8000:5ca1::278:93c:ab1';
        $model = ['ip' => '2405:8100:8000:5ca1::278:93c:ab2', 'user_id' => null, 'id' => $this->faker->numberBetween(0, 5000)];

        $cache = $this->partialMock(Repository::class, function (MockInterface $mock) use ($model) {
            $mock->shouldReceive('rememberForever')->andReturn(serialize([$model]));
        });

        $request = $this->partialMock(Request::class, function (MockInterface $mock) use ($ip) {
            $mock->shouldReceive('ip')->andReturn($ip);
        });

        $rules = new Rules($cache, $this->app[FirewallRepositoryInterface::class]);

        $this->assertNull($rules->find($request));
    }
}

<?php

namespace Tests\Legacy\LaravelIntegration\Validators;

use Tests\Legacy\LaravelIntegration\TestCase;
use Mockery;

class SpamValidatorTest extends TestCase
{
    public function testValidateSpamLink()
    {
        $auth = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $auth->shouldReceive('check')->andReturn(false);

        $request = request();

        $validator = new \Coyote\Http\Validators\SpamValidator($auth, $request);

        // not spam link
        $this->assertTrue($validator->validateSpamLink([], 'java.io', [1]));
        $this->assertTrue($validator->validateSpamLink([], 'asp.net', [1]));
        // spam links
        $this->assertFalse($validator->validateSpamLink([], 'www.java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'http://www.java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'https://www.java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'https://java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'http://java.io', [1]));
        $this->assertFalse($validator->validateSpamLink([], 'http://java.io/foo.html?x=y', [1]));

        $this->assertTrue($validator->validateSpamLink([], 'foo@bar.net', [1]));
    }

    private function buildValidatorInstance($ip, $authorized = false)
    {
        $request = Mockery::mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('getClientHost')->andReturn(gethostbyaddr($ip));

        app()->instance(\Illuminate\Http\Request::class, $request);

        $auth = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $auth->shouldReceive('check')->andReturn($authorized);

        return new \Coyote\Http\Validators\SpamValidator($auth, $request);
    }
}

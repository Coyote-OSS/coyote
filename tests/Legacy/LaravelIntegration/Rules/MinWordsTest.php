<?php

namespace Tests\Legacy\LaravelIntegration\Rules;

use Coyote\Rules\MinWords;
use Coyote\User;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LaravelIntegration\TestCase;

class MinWordsTest extends TestCase
{
    public function testShortTextShouldFail()
    {
        $rule = new MinWords(3);

        $this->assertFalse($rule->passes('', 'test'));
        $this->assertFalse($rule->passes('', 'test test'));
        $this->assertFalse($rule->passes('', 'test i test'));
        $this->assertTrue($rule->passes('', 'to jest test'));

        $user = factory(User::class)->make(['reputation' => 999]);

        Auth::setUser($user);

        $this->assertFalse($rule->passes('', 'test test'));
        $this->assertFalse($rule->passes('', 'test i test'));
        $this->assertTrue($rule->passes('', 'to jest test'));
    }

    public function testShortTextShouldPass()
    {
        $rule = new MinWords(3);
        $user = factory(User::class)->make(['reputation' => 1000]);

        Auth::setUser($user);

        $this->assertTrue($rule->passes('', 'test'));
        $this->assertTrue($rule->passes('', 'test test'));
        $this->assertTrue($rule->passes('', 'test i test'));
    }
}

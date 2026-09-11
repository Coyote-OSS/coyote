<?php

namespace Tests\Legacy\LaravelIntegration;

use Coyote\User;

class MacroTest extends TestCase
{
    public function testExceptUsersMacro()
    {
        $users = collect([(new User)->forceFill(['id' => 1]), (new User)->forceFill(['id' => 2]), (new User)->forceFill(['id' => 3])]);

        $result = $users->exceptUsers([(new User)->forceFill(['id' => 1])]);

        $this->assertEquals(2, count($result));
        $this->assertEquals(2, $result->first()->id);
        $this->assertEquals(3, $result->last()->id);

        $users = collect([(new User)->forceFill(['id' => 1])]);

        $result = $users->exceptUsers([(new User)->forceFill(['id' => 1]), (new User)->forceFill(['id' => 2]), (new User)->forceFill(['id' => 3])]);

        $this->assertEquals(0, count($result));
    }
}

<?php

namespace Tests\Legacy\LaravelIntegration\Services;

use Coyote\Block as Model;
use Coyote\Repositories\Contracts\BlockRepositoryInterface;
use Coyote\Services\TwigBridge\Extensions;
use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Mockery\MockInterface;
use Tests\Legacy\LaravelIntegration\TestCase;

class BlockTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    public function testShowBlockToAllUsers()
    {
        $model = new Model()->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText()]);

        $blockRepository = $this->partialMock(BlockRepositoryInterface::class, function (MockInterface $mock) use ($model) {
            $mock->shouldReceive('all')->andReturn(collect([$model]));
        });

        $block = resolve(Extensions\Block::class, ['blockRepository' => $blockRepository]);

        $this->getCacheFactory()->delete('blocks');
        $this->assertEquals($content, $block->renderBlock($name));

        $user = factory(User::class)->create();

        Auth::loginUsingId($user->id, true);

        $this->getCacheFactory()->delete('blocks');
        $this->assertEquals($content, $block->renderBlock($name));
    }

    protected function getCacheFactory(): \Illuminate\Contracts\Cache\Repository
    {
        return app(\Illuminate\Contracts\Cache\Repository::class);
    }

    public function testShowAdBlock()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText(), 'max_reputation' => 100]);

        $blockRepository = $this->partialMock(BlockRepositoryInterface::class, function (MockInterface $mock) use ($model) {
            $mock->shouldReceive('all')->andReturn(collect([$model]));
        });

        $block = resolve(Extensions\Block::class, ['blockRepository' => $blockRepository]);

        $this->getCacheFactory()->delete('blocks');
        $this->assertEquals($content, $block->renderBlock($name));

        $user = factory(User::class)->create();

        Auth::loginUsingId($user->id, true);

        $this->getCacheFactory()->delete('blocks');
        $this->assertEquals($content, $block->renderBlock($name));
    }

    public function testHideAdBlockForPrivilegeUser()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText(), 'max_reputation' => 100]);

        $blockRepository = $this->partialMock(BlockRepositoryInterface::class, function (MockInterface $mock) use ($model) {
            $mock->shouldReceive('all')->andReturn(collect([$model]));
        });

        $block = resolve(Extensions\Block::class, ['blockRepository' => $blockRepository]);

        $user = factory(User::class)->create(['reputation' => 101]);

        Auth::loginUsingId($user->id, true);

        $this->assertEquals('', $block->renderBlock($name));
    }

    public function testHideAdBlockForSponsor()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText(), 'enable_sponsor' => false]);

        $blockRepository = $this->partialMock(BlockRepositoryInterface::class, function (MockInterface $mock) use ($model) {
            $mock->shouldReceive('all')->andReturn(collect([$model]));
        });

        $block = resolve(Extensions\Block::class, ['blockRepository' => $blockRepository]);
        $user = factory(User::class)->create(['is_sponsor' => true]);

        Auth::loginUsingId($user->id, true);

        $this->assertEquals('', $block->renderBlock($name));
    }

    public function testShowAdBlockForRegularUser()
    {
        $model = (new Model())->forceFill(['name' => $name = $this->faker->text, 'content' => $content = $this->faker->realText(), 'enable_sponsor' => false]);

        $blockRepository = $this->partialMock(BlockRepositoryInterface::class, function (MockInterface $mock) use ($model) {
            $mock->shouldReceive('all')->andReturn(collect([$model]));
        });

        $block = resolve(Extensions\Block::class, ['blockRepository' => $blockRepository]);

        $this->getCacheFactory()->delete('blocks');
        $this->assertEquals($content, $block->renderBlock($name));

        $user = factory(User::class)->create();

        Auth::loginUsingId($user->id, true);

        $this->getCacheFactory()->delete('blocks');
        $this->assertEquals($content, $block->renderBlock($name));
    }
}

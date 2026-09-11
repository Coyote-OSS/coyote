<?php
namespace Tests\Legacy\Integration\Seo\Assets\Fixture;

use Coyote\Forum;
use Coyote\Models\Asset;
use Coyote\Post;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Http\Testing\FileFactory;
use Tests\Legacy\Integration\BaseFixture;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

trait Fixture
{
    use Laravel\Transactional;
    use BaseFixture\Forum\Store;

    function asset(string $name): int
    {
        $id = $this->uploadFile($name);
        $this->assignAssetContent($id, Post::class, $this->newPost());
        return $id;
    }

    function uploadFile(string $name): int
    {
        return $this->laravel
            ->actingAs($this->newUser())
            ->post('/assets', ['asset' => (new FileFactory)->create($name)])
            ->assertSuccessful()
            ->json('id');
    }

    function newPost(): int
    {
        return $this->storeThread(new Forum(), new Topic())->first_post_id;
    }

    function newUser(): User
    {
        /** @var User $first */
        $first = User::query()->where('name', 'irrelevant')->first();
        if ($first) {
            return $first;
        }
        $user = new User();
        $user->name = 'irrelevant';
        $user->email = 'irrelevant';
        $user->save();
        return $user;
    }

    function assignAssetContent(int $assetId, string $contentType, int $contentId): void
    {
        /** @var Asset $model */
        $model = Asset::query()->find($assetId);
        $model
            ->forceFill([
                'content_id'   => $contentId,
                'content_type' => $contentType,
            ])
            ->save();
    }
}

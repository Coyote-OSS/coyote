<?php
namespace Tests\Legacy\LaravelDusk;

use Carbon\Carbon;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Laravel\Dusk\Browser;

readonly class Driver
{
    public function closeGdprIfVisible(Browser $browser): void
    {
        $gdprButton = $browser->element('#gdpr-all');
        if ($gdprButton?->isDisplayed()) {
            $browser
                ->click('#gdpr-all')
                ->waitUntilMissing('.gdpr-modal');
        }
    }

    public function loginAsUserRegular(Browser $browser): void
    {
        $browser->loginAs($this->seedUser()->id);
    }

    public function loginAsUser(
        Browser $browser,
        string  $password = null,
        bool    $blocked = false,
        bool    $deleted = false,
        bool    $alpha = false,
    ): void
    {
        $user = $this->seedUser($password, $blocked, $deleted, $alpha);
        $browser->loginAs($user->id);
    }

    public function seedUser(
        string $password = null,
        bool   $blocked = false,
        bool   $deleted = false,
        bool   $alpha = false,
    ): User
    {
        $factory = factory(User::class);
        if ($alpha) {
            $factory = $factory->state('alpha');
        }
        return $factory->create([
            'password'   => bcrypt($password),
            'is_blocked' => $blocked,
            'deleted_at' => $deleted ? Carbon::now() : null,
        ]);
    }

    public function createTopicUrl(): string
    {
        return '/Forum/Newbie/Submit';
    }

    public function visit(Browser $browser, string $url): void
    {
        $browser->visit($url);
        $this->closeGdprIfVisible($browser);
    }

    public function typeMarkdown(Browser $browser, string $content): void
    {
        $browser
            ->element('.editor-4play .cm-editor .cm-content')
            ->clear()
            ->sendKeys($content);
    }

    public function seedTopic(string $mode): Topic
    {
        return factory(Topic::class)->create([
            'is_tree' => $mode === 'tree',
        ]);
    }

    public function topicUrl(Topic $topic): string
    {
        return UrlBuilder::topic($topic);
    }
}

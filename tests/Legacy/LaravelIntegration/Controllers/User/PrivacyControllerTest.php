<?php
namespace Tests\Legacy\LaravelIntegration\Controllers\User;

use Coyote\User;
use Tests\Legacy\LaravelIntegration\TestCase;

class PrivacyControllerTest extends TestCase
{
    /**
     * @test
     */
    public function initialState(): void
    {
        $this->assertGdprModal(false, $this->newUser());
    }

    /**
     * @test
     */
    public function guest(): void
    {
        $this->putJson('/User/Privacy', $this->gdprOptions())->assertStatus(403);
    }

    /**
     * @test
     */
    public function acceptUser(): void
    {
        $this
            ->actingAs($this->newUser())
            ->putJson('/User/Privacy', $this->gdprOptions())
            ->assertStatus(200);
    }

    /**
     * @test
     */
    public function agreeAll(): void
    {
        $user = $this->newUser();
        $this->actingAs($user)->putJson('/User/Privacy', $this->gdprOptions());
        $user->refresh();
        $this->assertGdprModal(true, $user);
    }

    /**
     * @test
     */
    public function agreeAllStoreInDatabase(): void
    {
        $user = $this->newUser();
        $this->actingAs($user)->putJson('/User/Privacy', $this->gdprOptions());
        $user->refresh();
        $this->assertGdprOptions(true, true, $user);
    }

    /**
     * @test
     */
    public function reset(): void
    {
        // given
        $user = $this->newUser();
        $this->actingAs($user)->putJson('/User/Privacy', $this->gdprOptions());
        $user->refresh();
        // when
        $this->actingAs($user)->get('/User/Privacy/Reset');
        $user->refresh();
        // then
        $this->assertGdprModal(false, $user);
    }

    /**
     * @test
     */
    public function resetRedirect(): void
    {
        $this
            ->actingAs($this->newUser())
            ->get('/User/Privacy/Reset')
            ->assertRedirect('/');
    }

    /**
     * @test
     */
    public function resetRedirectGuest(): void
    {
        $this
            ->get('/User/Privacy/Reset')
            ->assertRedirect('/');
    }

    /**
     * @test
     */
    public function declineAllStoreInDatabase(): void
    {
        $user = $this->newUser();
        $this->actingAs($user)->put('/User/Privacy', ['advertising' => false, 'analytics' => false]);
        $user->refresh();
        $this->assertGdprOptions(false, false, $user);
    }

    /**
     * @test
     */
    public function advertisingAllStoreInDatabase(): void
    {
        $user = $this->newUser();
        $this->actingAs($user)->put('/User/Privacy', ['advertising' => true, 'analytics' => false]);
        $user->refresh();
        $this->assertGdprOptions(true, false, $user);
    }

    /**
     * @test
     */
    public function missingAdvertising(): void
    {
        $this->putJson('/User/Privacy', ['analytics' => true])->assertStatus(422);
    }

    /**
     * @test
     */
    public function missingAnalytics(): void
    {
        $this->putJson('/User/Privacy', ['advertising' => true])->assertStatus(422);
    }

    private function assertGdprModal(bool $visible, User $user): void
    {
        $this->assertSame($visible, $this->gdprSubmitted($user));
    }

    private function newUser(): User
    {
        return factory(User::class)->create(['is_sponsor' => false]);
    }

    private function gdprSubmitted(User $user): bool
    {
        $content = $this->actingAs($user)->get('/')->getContent();
        return \strPos($content, '<div class="gdpr-modal modal"') === false;
    }

    private function assertGdprOptions(bool $advertising, bool $analytics, User $user): void
    {
        $expected = ['advertising' => $advertising, 'analytics' => $analytics];
        $this->assertDictionaryEqualIgnoreOrder($expected, \json_decode($user->gdpr, true));
    }

    public function assertDictionaryEqualIgnoreOrder(array $expected, array $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    private function gdprOptions(): array
    {
        return ['advertising' => true, 'analytics' => true];
    }
}

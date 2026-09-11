<?php

namespace Tests\Legacy\LaravelIntegration\Controllers;

use Coyote\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Legacy\LaravelIntegration\TestCase;

class GithubControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private function signature(array $data): string
    {
        return 'sha256=' . hash_hmac('sha256', json_encode($data), config('services.github.client_secret'));
    }

    public function testSetSuccessSponsorshipById()
    {
        $user = factory(User::class)->create(['provider' => 'Github', 'provider_id' => $this->faker->randomDigit]);

        $this->assertNotNull($user->provider);
        $this->assertNotNull($user->provider_id);

        $data = [
            'action' => 'created',
            'sender' => [
                'id' => $user->provider_id,
                'html_url' => 'https://github.com/lorem-ipsum'
            ]
        ];

        $signature = $this->signature($data);

        $response = $this->json('POST', '/github/sponsorship', $data, ['X-Hub-Signature-256' => $signature]);

        $response->assertStatus(200);

        $user->refresh();

        $this->assertTrue($user->is_sponsor);
    }

    public function testSetSuccessSponsorshipByGithubRepo()
    {
        $user = factory(User::class)->create(['github' => 'https://github.com/lorem-ipsum']);

        $data = [
            'action' => 'created',
            'sender' => [
                'id' => 1,
                'html_url' => 'https://github.com/lorem-ipsum'
            ]
        ];

        $signature = $this->signature($data);
        $response = $this->json('POST', '/github/sponsorship', $data, ['X-Hub-Signature-256' => $signature]);

        $response->assertStatus(200);

        $user->refresh();

        $this->assertTrue($user->is_sponsor);
    }

    public function testDowngradeSponsorship()
    {
        $user = factory(User::class)->create(['github' => 'https://github.com/lorem-ipsum', 'is_sponsor' => true]);

        $data = [
            'action' => 'cancelled',
            'sender' => [
                'id' => 1,
                'html_url' => 'https://github.com/lorem-ipsum'
            ]
        ];

        $signature = $this->signature($data);
        $response = $this->json('POST', '/github/sponsorship', $data, ['X-Hub-Signature-256' => $signature]);

        $response->assertStatus(200);

        $user->refresh();

        $this->assertFalse($user->is_sponsor);
    }
}

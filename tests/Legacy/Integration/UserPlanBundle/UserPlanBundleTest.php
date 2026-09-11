<?php
namespace Tests\Legacy\Integration\UserPlanBundle;

use Coyote\Models\UserPlanBundle;
use Coyote\Payment;
use Coyote\Plan;
use Coyote\User;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Legacy\Integration\BaseFixture\Server\Laravel;

class UserPlanBundleTest extends TestCase
{
    use Laravel\Transactional;

    #[Test]
    public function planDoesNotContainsBundle(): void
    {
        $plan = new Plan();
        $plan->name = 'no bundle';
        $plan->save();
        $this->assertNull($plan->bundle_size);
    }

    #[Test]
    public function planContainsBundle(): void
    {
        $plan = new Plan();
        $plan->name = 'no bundle';
        $plan->bundle_size = 3;
        $plan->save();
        $this->assertSame(3, $plan->bundle_size);
    }
}

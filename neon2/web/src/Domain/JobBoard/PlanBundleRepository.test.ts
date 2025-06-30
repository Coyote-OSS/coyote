import {assertEquals, assertThrows, describe, test} from "../../../test/assertion";
import {PlanBundleName, PricingPlan} from "./JobBoard";
import {PlanBundleRepository} from "./PlanBundleRepository";

describe('Plan bundle.', () => {
  test('Adding a bundle updates the view.', () => {
    const bundle = new PlanBundleRepository();
    let wasCalled = false;
    bundle.addListener({
      notify(plan: PlanBundleName, remainingJobOffers: number) {
        wasCalled = true;
      },
    });
    bundle.set('growth', 1);
    assertEquals(true, wasCalled);
  });

  test('Adding a bundle with a pricing plan and remaining job offers passes the bundle.', () => {
    const bundle = new PlanBundleRepository();
    bundle.addListener({
      notify(plan: PricingPlan, remainingJobOffers: number): void {
        assertEquals('strategic', plan);
        assertEquals(5, remainingJobOffers);
      },
    });
    bundle.set('strategic', 5);
  });

  test('Decreasing remaining job offers updates the view with a decreased value.', () => {
    const bundle = new PlanBundleRepository();
    bundle.set('strategic', 5);
    bundle.addListener({
      notify(plan: PricingPlan, remainingJobOffers: number): void {
        assertEquals(4, remainingJobOffers);
      },
    });
    bundle.decrease();
  });

  test('Fail to decrease a plan bundle that was not set.', () => {
    const bundle = new PlanBundleRepository();
    assertThrows(() => bundle.decrease(),
      'Failed to decrease a plan bundle, that was not set.');
  });
});

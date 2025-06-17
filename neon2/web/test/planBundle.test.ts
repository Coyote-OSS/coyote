import {PricingPlan} from "../src/neon3/Feature/JobBoard/Domain/Model";
import {PlanBundle} from "../src/planBundle";
import {assertEquals, assertThrows, describe, test} from "./assertion";

describe('Plan bundle.', () => {
  test('Adding a bundle updates the view.', () => {
    const bundle = new PlanBundle();
    let wasCalled = false;
    bundle.addListener(function () {
      wasCalled = true;
    });
    bundle.set('growth', 1);
    assertEquals(true, wasCalled);
  });

  test('Adding a bundle with a pricing plan and remaining job offers passes the bundle.', () => {
    const bundle = new PlanBundle();
    bundle.addListener(function (plan: PricingPlan, remainingJobOffers: number): void {
      assertEquals('strategic', plan);
      assertEquals(5, remainingJobOffers);
    });
    bundle.set('strategic', 5);
  });

  test('Decreasing remaining job offers updates the view with a decreased value.', () => {
    const bundle = new PlanBundle();
    bundle.set('strategic', 5);
    bundle.addListener(function (plan: PricingPlan, remainingJobOffers: number): void {
      assertEquals(4, remainingJobOffers);
    });
    bundle.decrease();
  });

  test('Fail to decrease a plan bundle that was not set.', () => {
    const bundle = new PlanBundle();
    assertThrows(() => bundle.decrease(),
      'Failed to decrease a plan bundle, that was not set.');
  });
});

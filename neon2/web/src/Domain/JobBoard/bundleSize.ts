import {PaidPricingPlan, PlanBundleName} from "./JobBoard";

export function bundleSize(pricingPlan: PaidPricingPlan): 1|3|5|20 {
  const bundleSizes: Record<PaidPricingPlan, 1|3|5|20> = {
    'premium': 1,
    'strategic': 3,
    'growth': 5,
    'scale': 20,
  };
  return bundleSizes[pricingPlan];
}

export function remainingJobOffers(planBundle: PlanBundleName): number {
  if (planBundle === 'strategic') {
    return 2;
  }
  if (planBundle === 'growth') {
    return 4;
  }
  if (planBundle === 'scale') {
    return 19;
  }
  throw new Error('Failed to set remaining job offers for a pricing plan.');
}

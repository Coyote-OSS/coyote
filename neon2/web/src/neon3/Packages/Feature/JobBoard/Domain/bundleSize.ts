import {PaidPricingPlan} from "./Model";

export function bundleSize(pricingPlan: PaidPricingPlan): 1|3|5|20 {
  const bundleSizes: Record<PaidPricingPlan, 1|3|5|20> = {
    'premium': 1,
    'strategic': 3,
    'growth': 5,
    'scale': 20,
  };
  return bundleSizes[pricingPlan];
}

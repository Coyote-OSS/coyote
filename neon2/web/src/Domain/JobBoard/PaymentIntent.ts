import {PaidPricingPlan} from "./JobBoard";

export interface PaymentIntent {
  paymentId: string;
  paymentPriceBase: number;
  paymentPriceVat: number;
  paymentPricingPlan: PaidPricingPlan;
}

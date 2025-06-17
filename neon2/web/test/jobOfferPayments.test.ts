import {JobOfferPayments} from "../src/jobOfferPayments";
import {PaidPricingPlan} from "../src/neon3/Feature/JobBoard/Domain/Model";
import {JobOfferPaymentIntent} from "../src/neon3/Feature/JobBoard/JobBoard";
import {assertEquals, describe, test} from "./assertion";

describe('Job offers association with payments.', () => {
  test('Read payment id for a job offer id', () => {
    const payments = new JobOfferPayments();
    payments.addJobOffer(paymentWithId('payment', 42));
    assertEquals(42, payments.jobOfferId('payment'));
  });
  test('Read job offer id by a payment id', () => {
    const payments = new JobOfferPayments();
    payments.addJobOffer(paymentWithId('payment', 25));
    assertEquals('payment', payments.paymentId(25));
  });
  test('Read job pricing plan by payment id', () => {
    const payments = new JobOfferPayments();
    payments.addJobOffer(paymentWithPlan('payment-id', 'growth'));
    assertEquals('growth', payments.pricingPlan('payment-id'));
  });
});

function paymentWithId(paymentId: string, jobOfferId: number): JobOfferPaymentIntent {
  return {
    jobOfferId,
    paymentIntent: {
      paymentId,
      paymentPriceBase: 0,
      paymentPriceVat: 0,
      paymentPricingPlan: 'premium',
    },
  };
}

function paymentWithPlan(paymentId: string, pricingPlan: PaidPricingPlan): JobOfferPaymentIntent {
  return {
    jobOfferId: 0,
    paymentIntent: {
      paymentId,
      paymentPriceBase: 0,
      paymentPriceVat: 0,
      paymentPricingPlan: pricingPlan,
    },
  };
}

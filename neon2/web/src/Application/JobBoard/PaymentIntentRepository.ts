import {JobOfferPayment, PaidPricingPlan} from "../../Domain/JobBoard/JobBoard";
import {JobOfferPaymentIntent} from "../../Domain/JobBoard/JobOfferPaymentIntent";

export class PaymentIntentRepository {
  private paymentIntents: JobOfferPaymentIntent[] = [];

  initJobOffers(jobOffers: JobOfferPaymentIntent[]): void {
    jobOffers.forEach(jobOffer => this.addJobOffer(jobOffer));
  }

  addJobOffer(jobOffer: JobOfferPaymentIntent): void {
    this.paymentIntents.push(jobOffer);
  }

  jobOfferId(paymentId: string): number {
    return this.jobOfferPaymentByPaymentId(paymentId).jobOfferId;
  }

  pricingPlan(paymentId: string): PaidPricingPlan {
    return this.jobOfferPaymentByPaymentId(paymentId).paymentIntent.paymentPricingPlan;
  }

  paymentId(jobOfferId: number): string {
    return this.byJobOfferId(jobOfferId)
      .paymentIntent
      .paymentId;
  }

  private byJobOfferId(jobOfferId: number): JobOfferPaymentIntent {
    return this.paymentIntents
      .find(payment => payment.jobOfferId === jobOfferId)!;
  }

  private jobOfferPaymentByPaymentId(paymentId: string): JobOfferPaymentIntent {
    return this.paymentIntents
      .find(payment => payment.paymentIntent.paymentId === paymentId)!;
  }

  jobOfferPayment(jobOfferId: number): JobOfferPayment {
    const intent = this.byJobOfferId(jobOfferId).paymentIntent;
    return {
      paymentPriceBase: intent.paymentPriceBase,
      paymentPriceVat: intent.paymentPriceVat,
      paymentPricingPlan: intent.paymentPricingPlan,
    };
  }
}

import {PaymentIntent} from "./PaymentIntent";

export interface JobOfferPaymentIntent {
  jobOfferId: number;
  paymentIntent: PaymentIntent;
}

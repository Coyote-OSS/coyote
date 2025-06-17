import {PaymentProvider} from "../Application/PaymentProvider";
import {StripePaymentProvider} from "../External/Stripe/StripePaymentProvider";
import {TestPaymentProvider} from "./TestPaymentProvider";

export function paymentProvider(testMode: boolean, stripeKey: string|null): PaymentProvider {
  return testMode
    ? new TestPaymentProvider()
    : new StripePaymentProvider(stripeKey!);
}

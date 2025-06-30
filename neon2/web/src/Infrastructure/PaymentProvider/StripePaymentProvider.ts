import {PaymentIntentResult, Stripe, StripeCardElement, StripeError} from '@stripe/stripe-js';
import {loadStripe} from '@stripe/stripe-js/pure';
import {PaymentMethod, PaymentNotification, PaymentProvider} from '../../Application/JobBoard/Port/PaymentProvider';

export class StripePaymentProvider implements PaymentProvider {
  private readonly _runtime: Promise<PaymentProvider>;

  constructor(publishableKey: string) {
    this._runtime = this.loadRuntime(publishableKey);
  }

  private async loadRuntime(key: string): Promise<PaymentProvider> {
    return new StripeRuntime((await loadStripe(key))!);
  }

  mountCardInput(cssSelector: string): void {
    this._runtime.then(runtime => runtime.mountCardInput(cssSelector));
  }

  unmountCardInput(): void {
    this._runtime.then(runtime => runtime.unmountCardInput());
  }

  performPayment(paymentToken: string, paymentMethod: PaymentMethod): Promise<PaymentNotification> {
    return this._runtime.then(runtime => runtime.performPayment(paymentToken, paymentMethod));
  }
}

class StripeRuntime implements PaymentProvider {
  private readonly cardElement: StripeCardElement;

  constructor(private stripe: Stripe) {
    this.cardElement = stripe.elements().create('card');
  }

  public mountCardInput(cssSelector: string): void {
    this.cardElement.mount(cssSelector);
  }

  public unmountCardInput(): void {
    this.cardElement.unmount();
  }

  public async performPayment(paymentToken: string, paymentMethod: PaymentMethod): Promise<PaymentNotification> {
    return this.parsePaymentResponse(await this.confirmPayment(paymentToken, paymentMethod));
  }

  private async confirmPayment(paymentToken: string, paymentMethod: PaymentMethod): Promise<PaymentIntentResult> {
    if (paymentMethod === 'card') {
      return await this.confirmCardPayment(paymentToken);
    }
    return await this.confirmP24Payment(paymentToken, 'wilkowski.kontakt@gmail.com');
  }

  private confirmCardPayment(paymentToken: string): Promise<PaymentIntentResult> {
    return this.stripe.confirmCardPayment(paymentToken, {
      payment_method: {card: this.cardElement},
    });
  }

  private confirmP24Payment(paymentToken: string, billingAddressEmail: string): Promise<PaymentIntentResult> {
    return this.stripe.confirmP24Payment(paymentToken, {
      payment_method: {billing_details: {email: billingAddressEmail}},
      return_url: window.location.href,
    });
  }

  private parsePaymentResponse(response: PaymentIntentResult): PaymentNotification {
    if (response.error) {
      return this.parseErrorResponse(response.error);
    }
    if (response.paymentIntent?.status === 'succeeded') {
      return 'accepted';
    }
    return 'unexpectedProviderResponse';
  }

  private parseErrorResponse(error: StripeError): PaymentNotification {
    if (error.code === 'expired_card') {
      return 'declinedCardExpired';
    }
    if (error.code === 'card_declined') {
      if (error.decline_code === 'generic_decline') {
        return 'declinedCard';
      }
      if (error.decline_code === 'insufficient_funds') {
        return 'declinedInsufficientFunds';
      }
    }
    return 'declinedPayment';
  }
}

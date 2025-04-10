export interface PaymentProvider {
  mountCardInput(cssSelector: string): void;
  unmountCardInput(): void;
  performPayment(paymentToken: string): Promise<PaymentNotification>;
}

export type PaymentNotification =
  |'processed'
  |'declinedPayment'
  |'declinedCard'
  |'declinedInsufficientFunds'
  |'declinedCardExpired'
  |'unexpectedProviderResponse';

export class Stripe implements PaymentProvider {
  private readonly stripe: stripe.Stripe;
  private readonly cardElement: stripe.elements.Element;

  constructor(private publicKey: string) {
    const stripe: stripe.StripeStatic = window['Stripe'];
    this.stripe = stripe(this.publicKey);
    this.cardElement = this.stripe.elements().create('card');
  }

  public mountCardInput(cssSelector: string): void {
    this.cardElement.mount(cssSelector);
  }

  public unmountCardInput(): void {
    this.cardElement.unmount();
  }

  public async performPayment(paymentToken: string): Promise<PaymentNotification> {
    return this.parsePaymentResponse(await this.confirmCardPayment(paymentToken));
  }

  private confirmCardPayment(paymentToken: string): Promise<stripe.PaymentIntentResponse> {
    return this.stripe.confirmCardPayment(paymentToken, {
      payment_method: {card: this.cardElement},
    });
  }

  private parsePaymentResponse(response: stripe.PaymentIntentResponse): PaymentNotification {
    if (response.error) {
      return this.parseErrorResponse(response);
    }
    if (response.paymentIntent) {
      if (response.paymentIntent.status === 'succeeded') {
        return 'processed';
      }
    }
    return 'unexpectedProviderResponse';
  }

  private parseErrorResponse(response: stripe.PaymentIntentResponse): PaymentNotification {
    if (response.error.code === 'expired_card') {
      return 'declinedCardExpired';
    }
    if (response.error.code === 'card_declined') {
      if (response.error.decline_code === 'generic_decline') {
        return 'declinedCard';
      }
      if (response.error.decline_code === 'insufficient_funds') {
        return 'declinedInsufficientFunds';
      }
    }
    return 'declinedPayment';
  }
}

export class TestPaymentProvider implements PaymentProvider {
  private input: HTMLInputElement|null = null;

  mountCardInput(cssSelector: string): void {
    this.input = document.createElement('input');
    this.input.dataset.testid = 'paymentProviderCard';
    this.input.maxLength = 16;
    const parent = document.querySelector(cssSelector)!;
    parent.appendChild(this.input);
  }

  unmountCardInput(): void {
    this.input!.remove();
  }

  async performPayment(paymentToken: string): Promise<PaymentNotification> {
    const cardNumber = this.cardNumber();
    const notification = this.paymentResultBasedOnCardNumber(cardNumber);
    const paymentStatus = notification === 'processed' ? 'completed' : 'failed';
    await callTestPaymentWebhook({paymentToken, paymentStatus});
    return notification;
  }

  private paymentResultBasedOnCardNumber(cardNumber: string): PaymentNotification {
    if (cardNumber === '4242424242424242') {
      return 'processed';
    }
    if (cardNumber === '4000000000009995') {
      return 'declinedInsufficientFunds';
    }
    if (cardNumber === '4000000000000069') {
      return 'declinedCardExpired';
    }
    if (cardNumber === '4000000000000002') {
      return 'declinedCard';
    }
    return 'declinedPayment';
  }

  private cardNumber(): string {
    return this.input.value;
  }
}

async function callTestPaymentWebhook(body: {paymentToken: string, paymentStatus: 'completed'|'failed'}): Promise<void> {
  await fetch('/neon2/webhook', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(body),
  });
}

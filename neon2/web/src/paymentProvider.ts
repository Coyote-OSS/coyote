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

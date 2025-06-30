import {PaymentMethod, PaymentNotification, PaymentProvider} from '../../Application/JobBoard/Port/PaymentProvider';

export class TestPaymentProvider implements PaymentProvider {
  private input: HTMLInputElement|null = null;

  mountCardInput(cssSelector: string): void {
    this.input = document.createElement('input');
    this.input.dataset.testid = 'paymentProviderCard';
    this.input.maxLength = 16;
    this.input.placeholder = 'Podaj numer karty';
    const parent = document.querySelector(cssSelector)!;
    parent.innerHTML = '';
    parent.appendChild(this.input);
  }

  unmountCardInput(): void {
    this.input!.remove();
  }

  async performPayment(paymentToken: string, paymentMethod: PaymentMethod): Promise<PaymentNotification> {
    if (paymentMethod === 'card') {
      return await this.performCardPayment(paymentToken);
    }
    if (paymentMethod === 'p24') {
      await callTestPaymentWebhook({paymentToken, paymentStatus: 'completed'});
      return 'accepted';
    }
    throw new Error('Failed to perform payment with given payment method.');
  }

  private async performCardPayment(paymentToken: string) {
    const cardNumber = this.cardNumber();
    const notification = this.paymentResultBasedOnCardNumber(cardNumber);
    const paymentStatus = notification === 'accepted' ? 'completed' : 'failed';
    await callTestPaymentWebhook({paymentToken, paymentStatus});
    return notification;
  }

  private paymentResultBasedOnCardNumber(cardNumber: string): PaymentNotification {
    if (cardNumber === '4242424242424242') {
      return 'accepted';
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
    return this.input!.value;
  }
}

type PaymentStatus = 'completed'|'failed';

async function callTestPaymentWebhook(body: {paymentToken: string, paymentStatus: PaymentStatus}): Promise<void> {
  for (let i = 0; i < 10; i++) {
    const responseOk = await tryRequestTestPaymentWebhook(body);
    if (responseOk) {
      return;
    }
    await wait(500);
  }
}

async function tryRequestTestPaymentWebhook(body: object): Promise<boolean> {
  const result = await fetch('/neon2/webhook', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(body),
  });
  return result.ok;
}

function wait(timeout: number): Promise<void> {
  return new Promise(resolve => {
    setTimeout(() => resolve(), timeout);
  });
}

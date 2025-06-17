export interface PaymentProvider {
  mountCardInput(cssSelector: string): void;
  unmountCardInput(): void;
  performPayment(paymentToken: string, paymentMethod: PaymentMethod): Promise<PaymentNotification>;
}

export type PaymentMethod = 'card'|'p24';
export type PaymentNotification =
  |'accepted'
  |'declinedPayment'
  |'declinedCard'
  |'declinedInsufficientFunds'
  |'declinedCardExpired'
  |'unexpectedProviderResponse';

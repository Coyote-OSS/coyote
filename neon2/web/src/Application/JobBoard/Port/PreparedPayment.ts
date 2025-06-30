export interface PreparedPayment {
  paymentId: string;
  providerReady: boolean;
  paymentToken: string|null;
}

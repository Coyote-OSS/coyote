import {PaymentUpdatedStatus} from "../../../Domain/JobBoard/JobBoard";

export type VatIdState = 'valid'|'invalid'|'pending';

export interface PaymentListener {
  processingStarted(): void;
  processingFinished(): void;
  paymentInitiationVatIdState(vatId: VatIdState): void;
  notificationReceived(notification: string): void;
  statusChanged(paymentId: string, status: PaymentUpdatedStatus): void;
}

import {VatIdState} from "../../../../../main";
import {PaymentStatus} from "../Domain/Model";

export interface PaymentListener {
  processingStarted(): void;
  processingFinished(): void;
  paymentInitiationVatIdState(vatId: VatIdState): void;
  notificationReceived(notification: string): void;
  statusChanged(paymentId: string, status: PaymentStatus): void;
}

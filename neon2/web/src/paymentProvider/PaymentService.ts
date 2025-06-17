import {JobBoardBackend} from "../backend";
import {InvoiceInformation, VatIdState} from "../main";
import {BackendPaymentStatus, BackendPreparedPayment} from "../neon3/Core/Backend/backendInput";
import {PaymentMethod, PaymentNotification, PaymentProvider} from "./PaymentProvider";

interface PaymentListener {
  processingStarted(): void;
  processingFinished(): void;
  paymentInitiationVatIdState(vatId: VatIdState): void;
  notificationReceived(notification: string): void;
  statusChanged(paymentId: string, status: PaymentStatus): void;
}

export type PaymentStatus = 'paymentComplete'|'paymentFailed';

export class PaymentService {
  private listeners: PaymentListener[] = [];

  constructor(private backend: JobBoardBackend, private provider: PaymentProvider) {}

  addEventListener(listener: PaymentListener) {
    this.listeners.push(listener);
  }

  async initiatePayment(paymentId: string, invoiceInfo: InvoiceInformation, paymentMethod: PaymentMethod): Promise<void> {
    this.listeners.forEach(listener => listener.processingStarted());
    const response = await this.backend.preparePayment(paymentId, invoiceInfo);
    this.listeners.forEach(listener => {
      const vatId = response.status === 'failedInvalidVatId' ? 'invalid' : 'valid';
      return listener.paymentInitiationVatIdState(vatId);
    });
    if (response.status === 'success') {
      const payment = response.preparedPayment!;
      const notification = await this.performPayment(payment, paymentMethod);
      this.updatePaymentNotification(notification);
      if (notification === 'accepted') {
        this.updatePaymentStatus(paymentId, await this.readPaymentStatus(payment.paymentId));
      } else {
        this.updatePaymentStatus(paymentId, 'paymentFailed');
      }
    }
    this.listeners.forEach(listener => listener.processingFinished());
  }

  private async readPaymentStatus(paymentId: string): Promise<PaymentStatus> {
    let counter = 0;
    while (true) {
      counter++;
      const status: BackendPaymentStatus = await this.backend.fetchPaymentStatus(paymentId);
      if (status !== 'awaitingPayment') {
        return status;
      }
      await new Promise(resolve => setTimeout(resolve, 500));
      if (counter >= 20) {
        break;
      }
    }
    return 'paymentFailed';
  }

  private performPayment(payment: BackendPreparedPayment, paymentMethod: PaymentMethod): Promise<PaymentNotification> {
    if (payment.providerReady) {
      return this.provider.performPayment(payment.paymentToken!, paymentMethod);
    }
    return Promise.resolve('unexpectedProviderResponse');
  }

  private updatePaymentNotification(notification: string): void {
    this.listeners.forEach(listener => listener.notificationReceived(notification));
  }

  private updatePaymentStatus(paymentId: string, status: PaymentStatus): void {
    this.listeners.forEach(listener => listener.statusChanged(paymentId, status));
  }
}

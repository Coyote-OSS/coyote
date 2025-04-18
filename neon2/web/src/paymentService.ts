import {BackendPaymentStatus, BackendPreparedPayment, JobBoardBackend} from "./backend";
import {InvoiceInformation} from "./main";
import {PaymentNotification, PaymentProvider} from "./paymentProvider";

export type PaymentStatus = 'paymentComplete'|'paymentFailed';

export class PaymentService {
  private listeners: PaymentListener[] = [];

  constructor(
    private backend: JobBoardBackend,
    private provider: PaymentProvider,
  ) {
  }

  addEventListener(listener: PaymentListener) {
    this.listeners.push(listener);
  }

  async initiatePayment(paymentId: string, invoiceInfo: InvoiceInformation): Promise<void> {
    const payment = await this.backend.preparePayment(paymentId, invoiceInfo);
    this.updatePaymentNotification(await this.performPayment(payment));
    this.updatePaymentStatus(paymentId, await this.readPaymentStatus(payment.paymentId));
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
      if (counter >= 10) {
        break;
      }
    }
    return 'paymentFailed';
  }

  private performPayment(payment: BackendPreparedPayment): Promise<PaymentNotification> {
    if (payment.providerReady) {
      return this.provider.performPayment(payment.paymentToken);
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

interface PaymentListener {
  notificationReceived(notification: string): void;
  statusChanged(paymentId: string, status: PaymentStatus): void;
}

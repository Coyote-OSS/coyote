import {InvoiceInformation, PaymentUpdatedStatus} from "../../Domain/JobBoard/JobBoard";
import {PaymentStatus} from "../../Domain/JobBoard/PaymentStatus";
import {ApplicationInbound} from "./Port/ApplicationInbound";
import {JobBoardApi} from "./Port/JobBoardApi";
import {PaymentListener} from "./Port/PaymentListener";
import {PaymentMethod, PaymentNotification, PaymentProvider} from "./Port/PaymentProvider";
import {PreparedPayment} from "./Port/PreparedPayment";

export class PaymentService {
  private listeners: PaymentListener[] = [];

  constructor(
    private inbound: ApplicationInbound,
    private jobBoardApi: JobBoardApi,
    private provider: PaymentProvider,
  ) {}

  addEventListener(listener: PaymentListener): void {
    this.listeners.push(listener);
  }

  async initiatePayment(paymentId: string, invoiceInfo: InvoiceInformation, paymentMethod: PaymentMethod): Promise<void> {
    this.listeners.forEach(listener => listener.processingStarted());
    const response = await this.jobBoardApi.preparePayment(
      this.inbound.userId(),
      paymentId,
      invoiceInfo);
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

  private async readPaymentStatus(paymentId: string): Promise<PaymentUpdatedStatus> {
    let counter = 0;
    while (true) {
      counter++;
      const status: PaymentStatus = await this.jobBoardApi.fetchPaymentStatus(paymentId);
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

  private performPayment(payment: PreparedPayment, paymentMethod: PaymentMethod): Promise<PaymentNotification> {
    if (payment.providerReady) {
      return this.provider.performPayment(payment.paymentToken!, paymentMethod);
    }
    return Promise.resolve('unexpectedProviderResponse');
  }

  private updatePaymentNotification(notification: string): void {
    this.listeners.forEach(listener => listener.notificationReceived(notification));
  }

  private updatePaymentStatus(paymentId: string, status: PaymentUpdatedStatus): void {
    this.listeners.forEach(listener => listener.statusChanged(paymentId, status));
  }
}

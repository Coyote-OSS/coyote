import {JobBoardBackend} from "../../../Core/Backend/JobBoardBackend";
import {PaymentMethod, PaymentNotification, PaymentProvider} from "../../../Core/Application/PaymentProvider";
import {BackendApi, BackendPreparedPayment} from "../../../Core/Backend/BackendApi";
import {BackendPaymentStatus} from "../../../Core/Backend/backendInput";
import {InvoiceInformation, PaymentStatus} from "../Domain/Model";
import {PaymentListener} from "./PaymentListener";

export class PaymentService {
  private listeners: PaymentListener[] = [];

  constructor(
    private backend: JobBoardBackend,
    private backendApi: BackendApi,
    private provider: PaymentProvider,
  ) {}

  addEventListener(listener: PaymentListener): void {
    this.listeners.push(listener);
  }

  async initiatePayment(paymentId: string, invoiceInfo: InvoiceInformation, paymentMethod: PaymentMethod): Promise<void> {
    this.listeners.forEach(listener => listener.processingStarted());
    const response = await this.backendApi.preparePayment(
      this.backend.userId(),
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

  private async readPaymentStatus(paymentId: string): Promise<PaymentStatus> {
    let counter = 0;
    while (true) {
      counter++;
      const status: BackendPaymentStatus = await this.backendApi.fetchPaymentStatus(paymentId);
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

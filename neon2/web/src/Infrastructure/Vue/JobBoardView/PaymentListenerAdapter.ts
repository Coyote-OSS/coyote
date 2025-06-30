import {PaymentNotification} from "../../../Application/JobBoard/Port/PaymentProvider";
import {PaymentListener, VatIdState} from "../../../Application/JobBoard/Port/PaymentListener";
import {PaymentUpdatedStatus} from "../../../Domain/JobBoard/JobBoard";
import {JobBoardService} from "./JobBoardService";
import {JobBoardView} from "./JobBoardView";

export class PaymentListenerAdapter implements PaymentListener {
  constructor(
    private readonly viewModel: JobBoardView,
    private readonly jobBoardService: JobBoardService,
  ) {}

  processingStarted(): void {
    this.viewModel.notifyPaymentProcessingStarted();
  }

  processingFinished(): void {
    this.viewModel.notifyPaymentProcessingFinished();
  }

  paymentInitiationVatIdState(vatId: VatIdState): void {
    this.viewModel.notifyPaymentVatIdState(vatId);
  }

  notificationReceived(notification: PaymentNotification): void {
    this.viewModel.notifyPaymentNotification(notification);
  }

  statusChanged(paymentId: string, status: PaymentUpdatedStatus): void {
    this.jobBoardService.paymentStatusChanged(paymentId, status);
  }
}

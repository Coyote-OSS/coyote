import {PaymentNotification} from "../../../../../Packages/Core/Application/PaymentProvider";
import {VatIdState} from "../../../../../Packages/Feature/JobBoard/Application/Model";
import {PaymentListener} from "../../../../../Packages/Feature/JobBoard/Application/PaymentListener";
import {PaymentStatus} from "../../../../../Packages/Feature/JobBoard/Domain/Model";
import {JobBoardService} from "../JobBoardService";
import {ViewModel} from "../ViewModel";

export class PaymentListenerAdapter implements PaymentListener {
  constructor(
    private readonly viewModel: ViewModel,
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

  statusChanged(paymentId: string, status: PaymentStatus): void {
    this.jobBoardService.paymentStatusChanged(paymentId, status);
  }
}

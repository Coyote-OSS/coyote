import {JobBoardBackend, toJobOffer} from "./backend";
import {JobBoard} from "./jobBoard";
import {ViewModel} from "./neon3/Apps/VueApp/Modules/JobBoard/ViewModel";
import {PaymentProvider} from "./neon3/Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./neon3/Packages/Core/Backend/BackendApi";
import {BackendJobOffer} from "./neon3/Packages/Core/Backend/backendInput";
import {isVatIncluded} from "./neon3/Packages/Core/Domain/vat";
import {InitiatePayment, SubmitJobOffer} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {PaymentIntentRepository} from "./neon3/Packages/Feature/JobBoard/Application/PaymentIntentRepository";
import {PaymentService} from "./neon3/Packages/Feature/JobBoard/Application/PaymentService";
import {PlanBundleRepository} from "./neon3/Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {bundleSize} from "./neon3/Packages/Feature/JobBoard/Domain/bundleSize";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {PaymentSummary, PricingPlan} from "./neon3/Packages/Feature/JobBoard/Domain/Model";
import {EventMetadata, ValuePropositionEvent} from "./neon3/Packages/Feature/Vp/Model";

export class JobOfferController {
  constructor(
    private backend: JobBoardBackend,
    private backendApi: BackendApi,
    private viewModel: ViewModel,
    private board: JobBoard,
    private paymentProvider: PaymentProvider,
    private payments: PaymentService,
    private jobOfferPayments: PaymentIntentRepository,
    private planBundleRepo: PlanBundleRepository,
  ) {}

  createJob(pricingPlan: PricingPlan, jobOffer: SubmitJobOffer): void {
    this.backendApi.addJobOffer(pricingPlan, jobOffer, (jobOffer: BackendJobOffer): void => {
      this.board.jobOfferCreated(toJobOffer(jobOffer));
      if (pricingPlan === 'free') {
        this.viewModel.notifyJobOfferCreatedFree(jobOffer.id);
      } else {
        this.jobOfferPayments.addJobOffer({jobOfferId: jobOffer.id, paymentIntent: jobOffer.payment!});
        this.viewModel.notifyJobOfferCreatedRequirePayment(
          jobOffer.id,
          this.paymentSummary(jobOffer.id));
      }
    });
  }

  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    this.viewModel.setJobOfferFavourite(jobOfferId, favourite);
    this.backendApi.markJobOfferAsFavourite(jobOfferId, favourite);
  }

  vatDetailsChanged(countryCode: string, vatId: string): void {
    this.viewModel.notifyVatIncludedChanged(isVatIncluded(countryCode, vatId));
  }

  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    this.backendApi.updateJobOffer(jobOfferId, jobOffer, (): void => {
      this.board.jobOfferUpdated(jobOfferId, jobOffer);
      this.viewModel.notifyJobOfferEdited(jobOfferId);
    });
  }

  payForJob(initiatePayment: InitiatePayment): void {
    this.payments.initiatePayment(
      this.jobOfferPayments.paymentId(initiatePayment.jobOfferId),
      initiatePayment.invoiceInfo,
      initiatePayment.paymentMethod);
  }

  resumePayment(jobOfferId: number): void {
    this.viewModel.initRequirePayment(this.paymentSummary(jobOfferId));
  }

  redeemBundle(jobOfferId: number): void {
    this.backendApi
      .publishJobOfferUsingBundle(jobOfferId, this.backend.userId())
      .then(() => {
        this.board.jobOfferPaid(jobOfferId);
        this.viewModel.notifyPlanBundleUsed();
        this.planBundleRepo.decrease();
      });
  }

  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void {
    if (action === 'mount') {
      this.paymentProvider.mountCardInput(cssSelector!);
    } else {
      this.paymentProvider.unmountCardInput();
    }
  }

  selectPlan(plan: PricingPlan): void {
    if (this.backend.isAuthenticated()) {
      this.viewModel.notifyPlanSelected(plan);
    } else {
      window.location.href = '/Login';
    }
  }

  apply(jobOffer: JobOffer): void {
    this.viewModel.showValueProposition(jobOffer);
  }

  valuePropositionAccepted(
    jobOffer: JobOffer,
    event: ValuePropositionEvent,
    email?: string,
  ): void {
    const result = this.vpEvent(event, {jobOfferId: jobOffer.id, email});
    if (event === 'vpDeclined' || event === 'vpApply') {
      this.viewModel.hideValueProposition();
      result.finally(() => this.jobOfferApply(jobOffer));
    }
  }

  private paymentSummary(jobOfferId: number): PaymentSummary {
    const payment = this.jobOfferPayments.jobOfferPayment(jobOfferId);
    return {
      bundleSize: bundleSize(payment.paymentPricingPlan),
      basePrice: payment.paymentPriceBase,
      vat: payment.paymentPriceVat,
      vatIncluded: true,
    };
  }

  private jobOfferApply(jobOffer: JobOffer): void {
    if (jobOffer.applicationMode === 'external-ats') {
      window.open(jobOffer.applicationUrl, '_blank');
    } else {
      window.location.href = jobOffer.applicationUrl;
    }
  }

  private vpEvent(eventName: string, metadata: EventMetadata): Promise<void> {
    return this.backendApi.event({eventName, metadata});
  }
}

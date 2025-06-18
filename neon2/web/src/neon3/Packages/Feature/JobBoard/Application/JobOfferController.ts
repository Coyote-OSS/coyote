import {ViewModel} from "../../../../Apps/VueApp/Modules/JobBoard/ViewModel";
import {PaymentProvider} from "../../../Core/Application/PaymentProvider";
import {BackendApi} from "../../../Core/Backend/BackendApi";
import {BackendPaymentIntent} from "../../../Core/Backend/backendInput";
import {JobBoardBackend} from "../../../Core/Backend/JobBoardBackend";
import {isVatIncluded} from "../../../Core/Domain/vat";
import {EventMetadata, ValuePropositionEvent} from "../../Vp/Model";
import {bundleSize, remainingJobOffers} from "../Domain/bundleSize";
import {JobOffer} from "../Domain/JobOffer";
import {PaymentStatus, PaymentSummary, PricingPlan} from "../Domain/Model";
import {FilterRepository} from "./FilterRepository";
import {JobOfferFilterService} from "./JobOfferFilterService";
import {JobOfferRepository} from "./JobOfferRepository";
import {InitiatePayment, SubmitJobOffer} from "./Model";
import {PaymentIntentRepository} from "./PaymentIntentRepository";
import {PaymentService} from "./PaymentService";
import {PlanBundleRepository} from "./PlanBundleRepository";

export class JobOfferController {
  constructor(
    private backend: JobBoardBackend,
    private backendApi: BackendApi,
    private viewModel: ViewModel,
    private paymentProvider: PaymentProvider,
    private payments: PaymentService,
    private paymentIntents: PaymentIntentRepository,
    private planBundleRepo: PlanBundleRepository,
    private jobOffersRepo: JobOfferRepository,
    private filterService: JobOfferFilterService,
    private filterRepo: FilterRepository,
  ) {}

  createJob(pricingPlan: PricingPlan, submit: SubmitJobOffer): void {
    this.backendApi
      .addJobOffer(
        pricingPlan,
        submit,
        (jobOffer: JobOffer, payment: BackendPaymentIntent|null): void => {
          this.jobOffersRepo.insertFirst(jobOffer);
          this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
          if (pricingPlan === 'free') {
            this.viewModel.notifyJobOfferCreatedFree(jobOffer.id);
          } else {
            this.paymentIntents.addJobOffer({jobOfferId: jobOffer.id, paymentIntent: payment!});
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

  initJobOffers(jobOffers: JobOffer[]) :void{
    this.jobOffersRepo.setJobOffers(jobOffers);
    this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
  }

  vatDetailsChanged(countryCode: string, vatId: string): void {
    this.viewModel.notifyVatIncludedChanged(isVatIncluded(countryCode, vatId));
  }

  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    this.backendApi.updateJobOffer(jobOfferId, jobOffer, (): void => {
      this.jobOffersRepo.updateJobOffer(jobOfferId, jobOffer);
      this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
      this.viewModel.notifyJobOfferEdited(jobOfferId);
    });
  }

  payForJob(initiatePayment: InitiatePayment): void {
    this.payments.initiatePayment(
      this.paymentIntents.paymentId(initiatePayment.jobOfferId),
      initiatePayment.invoiceInfo,
      initiatePayment.paymentMethod);
  }

  resumePayment(jobOfferId: number): void {
    this.viewModel.initRequirePayment(this.paymentSummary(jobOfferId));
  }

  paymentStatusChanged(paymentId: string, status: PaymentStatus): void {
    this.viewModel.setPaymentStatus(status);
    if (status === 'paymentComplete') {
      this.jobOffersRepo.updateJobOfferPublished(this.paymentIntents.jobOfferId(paymentId));
      this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
      const pricingPlan = this.paymentIntents.pricingPlan(paymentId);
      if (pricingPlan !== 'premium') {
        this.planBundleRepo.set(pricingPlan, remainingJobOffers(pricingPlan));
      }
      this.viewModel.notifyJobOfferPaid();
    }
  }

  redeemBundle(jobOfferId: number): void {
    this.backendApi
      .publishJobOfferUsingBundle(jobOfferId, this.backend.userId())
      .then(() => {
        this.jobOffersRepo.updateJobOfferPublished(jobOfferId);
        this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
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
    const payment = this.paymentIntents.jobOfferPayment(jobOfferId);
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

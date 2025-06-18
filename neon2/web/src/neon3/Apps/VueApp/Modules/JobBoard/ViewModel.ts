import {PaymentNotification} from "../../../../Packages/Core/Application/PaymentProvider";
import {FilterOptions} from "../../../../Packages/Feature/JobBoard/Application/filter";
import {JobBoardListener} from "../../../../Packages/Feature/JobBoard/Application/JobBoardListener";
import {VatIdState} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {
  Country,
  PaymentStatus,
  PaymentSummary,
  PlanBundleName,
  PricingPlan,
} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {BoardStore} from "./store";

export class ViewModel implements JobBoardListener {
  constructor(private readonly store: BoardStore) {}

  notifyJobOfferEdited(jobOfferId: number): void {
    this.store.toast = 'edited';
  }

  notifyJobOfferCreatedFree(jobOfferId: number): void {
    this.store.toast = 'created';
  }

  notifyJobOfferCreatedRequirePayment(
    jobOfferId: number,
    summary: PaymentSummary,
  ): void {
    this.store.toast = 'created';
    this.store.paymentSummary = summary;
  }

  notifyPlanBundleUsed(): void {
    this.store.toast = 'bundle-used';
  }

  notifyPlanSelected(plan: PricingPlan): void {
    this.store.pricingPlan = plan;
  }

  notifyPaymentProcessingStarted(): void {
    this.store.paymentProcessing = true;
    this.store.paymentVatIdState = 'pending';
  }

  notifyPaymentProcessingFinished(): void {
    this.store.paymentProcessing = false;
  }

  notifyPaymentVatIdState(vatId: VatIdState): void {
    this.store.paymentVatIdState = vatId;
  }

  notifyPaymentNotification(notification: PaymentNotification): void {
    this.store.paymentNotification = notification;
  }

  setPaymentStatus(status: PaymentStatus): void {
    this.store.paymentStatus = status;
  }

  notifyVatIncludedChanged(vatIncluded: boolean): void {
    this.store.paymentSummary!.vatIncluded = vatIncluded;
  }

  initRequirePayment(summary: PaymentSummary): void {
    this.store.paymentSummary = summary;
  }

  initPaymentInvoiceCountries(countries: Country[]): void {
    this.store.invoiceCountries = countries;
  }

  initJobOfferApplicationEmail(applicationEmail: string): void {
    this.store.applicationEmail = applicationEmail;
  }

  notifyPlanBundleChanged(bundleName: PlanBundleName, remainingJobOffers: number, canRedeem: boolean): void {
    this.store.planBundle = {bundleName, remainingJobOffers, canRedeem};
    this.store.pricingPlan = bundleName;
  }

  showValueProposition(jobOffer: JobOffer): void {
    this.store.vpVisibleFor = jobOffer;
  }

  hideValueProposition(): void {
    this.store.vpVisibleFor = null;
  }

  setFiltersOptions(filters: FilterOptions): void {
    this.store.jobOfferFilters = filters;
  }

  setJobOfferFavourite(jobOfferId: number, favourite: boolean): void {
    const jobOffer = this.store.jobOffers.find(o => o.id === jobOfferId);
    if (jobOffer) {
      jobOffer.isFavourite = favourite;
    } else {
      throw new Error('Failed to mark job offer.');
    }
  }

  notifyJobOffersChanged(jobOffers: JobOffer[]): void {
    this.store.jobOffers = jobOffers;
  }
}

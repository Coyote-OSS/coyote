import {Filter, FilterOptions} from "../../../Application/JobBoard/filter";
import {JobBoardListener} from "../../../Application/JobBoard/Port/JobBoardListener";
import {VatIdState} from "../../../Application/JobBoard/Port/PaymentListener";
import {PaymentNotification} from "../../../Application/JobBoard/Port/PaymentProvider";
import {PricingPlanPort} from "../../../Application/JobBoard/Port/PricingPlanPort";
import {
  Country,
  PaymentSummary,
  PaymentUpdatedStatus,
  PlanBundleName,
  PricingPlan,
} from "../../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../../Domain/JobBoard/JobOffer";
import {BoardStore, useBoardStore} from "./boardStore";

export class JobBoardView implements JobBoardListener, PricingPlanPort {
  private readonly store: BoardStore = useBoardStore();

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

  setPaymentStatus(status: PaymentUpdatedStatus): void {
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

  clearToast(): void {
    this.store.toast = null;
  }

  setFilter(filter: Filter): void {
    this.store.jobOfferFilter = filter;
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.store.jobOffers = jobOffers;
  }

  pricingPlanSelected(): boolean {
    return this.store.$state.pricingPlan !== null;
  }
}

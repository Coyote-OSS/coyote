import {VatIdState} from "../../../../../main";
import {Screens} from "../../../../../Screens";
import {PaymentNotification} from "../../../../Packages/Core/Application/PaymentProvider";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {Country, PaymentSummary} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {BoardStore} from "./store";

export class JobBoardPresenter {
  constructor(
    private readonly store: BoardStore,
    private readonly screens: Screens,
  ) {}

  notifyJobOfferEdited(jobOfferId: number): void {
    this.store.toast = 'edited';
    this.navigateHome();
  }

  notifyJobOfferCreatedFree(jobOfferId: number): void {
    this.store.toast = 'created';
    this.navigateHome();
  }

  notifyJobOfferCreatedRequirePayment(
    jobOfferId: number,
    summary: PaymentSummary,
  ): void {
    this.store.toast = 'created';
    this.store.paymentSummary = summary;
    this.screens.navigate('payment', jobOfferId);
  }

  notifyJobOfferPaid(): void {
    this.navigateHome();
  }

  notifyPlanBundleUsed(): void {
    this.store.toast = 'bundle-used';
    this.navigateHome();
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

  showValueProposition(jobOffer: JobOffer): void {
    this.store.vpVisibleFor = jobOffer;
  }

  hideValueProposition(): void {
    this.store.vpVisibleFor = null;
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.store.jobOffers = jobOffers;
  }

  private navigateHome(): void {
    this.screens.navigate('home', null);
  }
}

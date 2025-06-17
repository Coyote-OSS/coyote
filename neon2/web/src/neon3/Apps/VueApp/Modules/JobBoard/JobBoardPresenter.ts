import {Screens} from "../../../../../Screens";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {PaymentSummary} from "../../../../Packages/Feature/JobBoard/Presenter/Model";
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

  populateRequirePayment(summary: PaymentSummary): void {
    this.store.paymentSummary = summary;
  }

  showValueProposition(jobOffer: JobOffer): void {
    this.store.vpVisibleFor = jobOffer;
  }

  hideValueProposition(): void {
    this.store.vpVisibleFor = null;
  }

  private navigateHome(): void {
    this.screens.navigate('home', null);
  }
}

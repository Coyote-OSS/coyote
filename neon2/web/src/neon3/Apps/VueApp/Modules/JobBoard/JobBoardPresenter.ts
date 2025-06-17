import {Screens} from "../../../../../Screens";
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

  private navigateHome(): void {
    this.screens.navigate('home', null);
  }
}

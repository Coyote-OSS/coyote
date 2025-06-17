import {JobOfferFilter} from "../../../../../jobOfferFilter";
import {Screen, TagAutocomplete, TagAutocompleteResult, UiController, ViewListener} from "../../../../../view/ui/ui";
import {InitiatePayment, SubmitJobOffer} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {BoardStore} from "./store";

export class JobBoardService {
  constructor(
    private store: BoardStore,
    private viewListener: ViewListener,
    private uiController: UiController,
    private _tagAutocomplete: TagAutocomplete,
  ) {}

  redeemBundle(jobOfferId: number): void {
    this.viewListener.redeemBundle(jobOfferId);
  }

  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    this.viewListener.updateJob(jobOfferId, jobOffer);
  }

  createJob(plan: PricingPlan, jobOffer: SubmitJobOffer): void {
    this.viewListener.createJob(plan, jobOffer);
  }

  showJob(
    jobOfferId: number,
    /** @deprecated **/
    jobOffer: JobOffer,
  ): void {
    this.uiController.showJobOffer(jobOffer!);
  }

  showForm(): void {
    this.uiController.showForm();
  }

  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void {
    this.viewListener.mountLocationDisplay(element, latitude, longitude);
  }

  filter(filter: JobOfferFilter): void {
    this.uiController.filter(filter);
  }

  navigate(screen: Screen, jobOfferId: number|null): void {
    this.uiController.navigate(screen, jobOfferId);
  }

  applyForJob(jobOfferId: number): void {
    this.uiController.applyForJob(jobOfferId);
  }

  findJobOffer(jobOfferId: number): JobOffer|null {
    return this.uiController.findJobOffer(jobOfferId);
  }

  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    this.uiController.markAsFavourite(jobOfferId, favourite);
  }

  selectPlan(plan: PricingPlan): void {
    this.uiController.selectPlan(plan);
  }

  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void {
    this.viewListener.managePaymentMethod(action, cssSelector);
  }

  payForJob(payment: InitiatePayment): void {
    this.viewListener.payForJob(payment);
  }

  vatDetailsChanged(countryCode: string, vatId: string): void {
    this.viewListener.vatDetailsChanged(countryCode, vatId);
  }

  filterOnlyMine(onlyMine: boolean): void {
    this.uiController.filterOnlyMine(onlyMine);
  }

  /** @deprecated */
  jobOfferUrl(jobOffer: JobOffer): string {
    return this.uiController.jobOfferUrl(jobOffer);
  }

  tagAutocomplete(tagPrompt: string, result: TagAutocompleteResult): void {
    this._tagAutocomplete(tagPrompt, result);
  }
}

import {UploadAssets, ValuePropositionEvent} from "../../../../../main";
import {Screens} from "../../../../../view/ui/screen/Screens";
import {
  FilterListener,
  NavigationListener,
  Screen,
  TagAutocomplete,
  TagAutocompleteResult,
  ViewListener,
  VueUi,
} from "../../../../../view/ui/ui";
import {View} from "../../../../../view/view";
import {LocationInput, LocationListener} from "../../../../Packages/Core/Application/LocationInput";
import {Filter} from "../../../../Packages/Feature/JobBoard/Application/filter";
import {InitiatePayment, SubmitJobOffer} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {BoardStore} from "./store";

export class JobBoardService {
  constructor(
    private store: BoardStore,
    private view: View,
    private readonly screens: Screens,
    private locationInput: LocationInput,
    private viewListener: ViewListener,
    private ui: VueUi,
    private _tagAutocomplete: TagAutocomplete,
    private upload: UploadAssets,
    private readonly filterListeners: FilterListener[],
    private navigationListener: NavigationListener,
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
    this.screens.showJobOffer(jobOffer!);
  }

  showForm(): void {
    this.navigationListener!.showJobOfferForm();
  }

  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void {
    this.viewListener.mountLocationDisplay(element, latitude, longitude);
  }

  filter(filter: Filter): void {
    this.filterListeners.forEach(listener => listener.filter(filter));
  }

  navigate(screen: Screen, jobOfferId: number|null): void {
    this.navigationListener!.setScreen(screen, jobOfferId);
  }

  applyForJob(jobOfferId: number): void {
    this.viewListener!.apply(this.findJobOffer(jobOfferId)!);
  }

  findJobOffer(jobOfferId: number): JobOffer|null {
    return this.view!.findJobOffer(jobOfferId);
  }

  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    this.viewListener!.markAsFavourite(jobOfferId, favourite);
  }

  selectPlan(plan: PricingPlan): void {
    this.ui.selectPlan(plan);
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
    this.filterListeners.forEach(listener => listener.filterOnlyMine(onlyMine));
  }

  /** @deprecated */
  jobOfferUrl(jobOffer: JobOffer): string {
    return this.screens.jobOfferUrl(jobOffer);
  }

  tagAutocomplete(tagPrompt: string, result: TagAutocompleteResult): void {
    this._tagAutocomplete(tagPrompt, result);
  }

  uploadLogo(file: File): Promise<string> {
    return this.upload.uploadLogo(file);
  }

  uploadAsset(file: File): Promise<string> {
    return this.upload.uploadAsset(file);
  }

  valuePropositionAccepted(
    event: ValuePropositionEvent,
    email?: string,
  ): void {
    this.viewListener!.valuePropositionAccepted(this.store!.vpVisibleFor!, event, email);
  }

  mountLocationInput(input: HTMLInputElement, listener: LocationListener): void {
    this.locationInput.mount(input, listener);
  }
}

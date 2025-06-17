import {JobOfferFilterService} from "../../../../Packages/Feature/JobBoard/Application/JobOfferFilterService";
import {ValuePropositionEvent} from "../../../../../main";
import {Screens} from "../../../../../Screens";
import {Screen, TagAutocomplete, TagAutocompleteResult, ViewListener, VueUiFactory} from "../../../../../ui";
import {LocationInput, LocationListener} from "../../../../Packages/Core/Application/LocationInput";
import {BackendImageApi} from "../../../../Packages/Core/Backend/BackendImageApi";
import {Filter} from "../../../../Packages/Feature/JobBoard/Application/filter";
import {FilterRepository} from "../../../../Packages/Feature/JobBoard/Application/FilterRepository";
import {JobOfferRepository} from "../../../../Packages/Feature/JobBoard/Application/JobOfferRepository";
import {InitiatePayment, SubmitJobOffer} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {PlanBundleRepository} from "../../../../Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {BoardStore} from "./store";

export class JobBoardService {
  constructor(
    private readonly ui: VueUiFactory,
    private readonly store: BoardStore,
    private readonly screens: Screens,
    private readonly locationInput: LocationInput,
    private readonly viewListener: ViewListener,
    private readonly _tagAutocomplete: TagAutocomplete,
    private readonly backendImageApi: BackendImageApi,
    private readonly allJobOffers: JobOfferRepository,
    private readonly planBundle: PlanBundleRepository,
    private readonly filterRepo: FilterRepository,
    private readonly filterService: JobOfferFilterService,
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

  showJob(jobOfferId: number): void {
    this.screens.showJobOffer(this.ui.findJobOffer(jobOfferId)!);
  }

  showForm(): void {
    if (this.planBundle.canRedeem()) {
      this.screens.navigate('form', null);
    } else {
      this.ui.screens.navigate('pricing', null);
    }
  }

  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void {
    this.viewListener.mountLocationDisplay(element, latitude, longitude);
  }

  filter(filter: Filter): void {
    this.filterRepo.setFilter(filter);
    this.store.jobOfferFilter = filter;

    this.store.jobOffers = this.filterService.filter(this.filterRepo);
  }

  filterOnlyMine(onlyMine: boolean): void {
    this.filterRepo.setFilterOnlyMine(onlyMine);

    this.store.jobOffers = this.filterService.filter(this.filterRepo);
  }

  navigate(screen: Screen, jobOfferId: number|null): void {
    this.store.toast = null;
    this.screens.navigate(screen, jobOfferId);
  }

  applyForJob(jobOfferId: number): void {
    this.viewListener!.apply(this.findJobOffer(jobOfferId)!);
  }

  findJobOffer(jobOfferId: number): JobOffer|null {
    return this.allJobOffers.findJobOffer(jobOfferId);
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

  /** @deprecated */
  jobOfferUrl(jobOffer: JobOffer): string {
    return this.screens.jobOfferUrl(jobOffer);
  }

  tagAutocomplete(tagPrompt: string, result: TagAutocompleteResult): void {
    this._tagAutocomplete(tagPrompt, result);
  }

  uploadLogo(file: File): Promise<string> {
    return this.backendImageApi.uploadLogoReturnUrl(file);
  }

  uploadAsset(file: File): Promise<string> {
    return this.backendImageApi.uploadAssetReturnUrl(file);
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

  resumePayment(jobOfferId: number): void {
    this.viewListener!.resumePayment(jobOfferId);
  }
}

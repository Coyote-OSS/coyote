import {JobOfferController} from "../../../../../JobOfferController";
import {Screens} from "../../../../../Screens";
import {LocationDisplay} from "../../../../Packages/Core/Application/LocationDisplay";
import {LocationInput, LocationListener} from "../../../../Packages/Core/Application/LocationInput";
import {BackendImageApi} from "../../../../Packages/Core/Backend/BackendImageApi";
import {Filter} from "../../../../Packages/Feature/JobBoard/Application/filter";
import {FilterRepository} from "../../../../Packages/Feature/JobBoard/Application/FilterRepository";
import {JobOfferFilterService} from "../../../../Packages/Feature/JobBoard/Application/JobOfferFilterService";
import {JobOfferRepository} from "../../../../Packages/Feature/JobBoard/Application/JobOfferRepository";
import {InitiatePayment, SubmitJobOffer} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {PlanBundleRepository} from "../../../../Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {
  TagAutocomplete,
  TagAutocompleteResult,
} from "../../../../Packages/Feature/JobBoard/Application/TagAutocomplete";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {Screen} from "../../../../Packages/Feature/JobBoard/Presenter/Model";
import {ValuePropositionEvent} from "../../../../Packages/Feature/Vp/Model";
import {BoardStore} from "./store";

export class JobBoardService {
  constructor(
    private readonly store: BoardStore,
    private readonly screens: Screens,
    private readonly locationInput: LocationInput,
    private readonly locationDisplay: LocationDisplay,
    private readonly controller: JobOfferController,
    private readonly _tagAutocomplete: TagAutocomplete,
    private readonly backendImageApi: BackendImageApi,
    private readonly jobOfferRepo: JobOfferRepository,
    private readonly planBundle: PlanBundleRepository,
    private readonly filterRepo: FilterRepository,
    private readonly filterService: JobOfferFilterService,
  ) {}

  redeemBundle(jobOfferId: number): void {
    this.controller.redeemBundle(jobOfferId);
  }

  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    this.controller.updateJob(jobOfferId, jobOffer);
  }

  createJob(plan: PricingPlan, jobOffer: SubmitJobOffer): void {
    this.controller.createJob(plan, jobOffer);
  }

  applyForJob(jobOfferId: number): void {
    this.controller!.apply(this.findJobOffer(jobOfferId)!);
  }

  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    this.controller!.markAsFavourite(jobOfferId, favourite);
  }

  selectPlan(plan: PricingPlan): void {
    this.controller.selectPlan(plan);
  }

  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void {
    this.controller.managePaymentMethod(action, cssSelector);
  }

  payForJob(payment: InitiatePayment): void {
    this.controller.payForJob(payment);
  }

  vatDetailsChanged(countryCode: string, vatId: string): void {
    this.controller.vatDetailsChanged(countryCode, vatId);
  }

  resumePayment(jobOfferId: number): void {
    this.controller!.resumePayment(jobOfferId);
  }

  valuePropositionAccepted(
    event: ValuePropositionEvent,
    email?: string,
  ): void {
    this.controller!.valuePropositionAccepted(this.store!.vpVisibleFor!, event, email);
  }

  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void {
    this.locationDisplay.mount(element, latitude, longitude);
  }

  showJob(jobOfferId: number): void {
    this.screens.showJobOffer(this.jobOfferRepo.findJobOffer(jobOfferId)!);
  }

  showForm(): void {
    if (this.planBundle.canRedeem()) {
      this.screens.navigate('form', null);
    } else {
      this.screens.navigate('pricing', null);
    }
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

  findJobOffer(jobOfferId: number): JobOffer|null {
    return this.jobOfferRepo.findJobOffer(jobOfferId);
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

  mountLocationInput(input: HTMLInputElement, listener: LocationListener): void {
    this.locationInput.mount(input, listener);
  }
}

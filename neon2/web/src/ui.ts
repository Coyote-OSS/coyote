import {createPinia} from "pinia";
import {createApp} from 'vue';
import JobBoard from './JobBoard.vue';
import {ValuePropositionEvent} from "./main";
import {JobBoardService} from "./neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {BoardStore, useBoardStore} from "./neon3/Apps/VueApp/Modules/JobBoard/store";
import {jobBoardServiceInjectKey} from "./neon3/Apps/VueApp/Modules/JobBoard/vue";
import {LocationInput} from "./neon3/Packages/Core/Application/LocationInput";
import {BackendImageApi} from "./neon3/Packages/Core/Backend/BackendImageApi";
import {Filter, FilterOptions} from "./neon3/Packages/Feature/JobBoard/Application/filter";
import {FilterRepository} from "./neon3/Packages/Feature/JobBoard/Application/FilterRepository";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {InitiatePayment, SubmitJobOffer} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {PlanBundleRepository} from "./neon3/Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {PaymentStatus, PlanBundleName, PricingPlan, Tag} from "./neon3/Packages/Feature/JobBoard/Domain/Model";
import {Policy} from "./Policy";
import {Screens} from "./Screens";
import {View} from "./view";

export type Screen = 'home'|'edit'|'form'|'payment'|'pricing'|'show';

export interface ViewListener {
  createJob(plan: PricingPlan, jobOffer: SubmitJobOffer): void;
  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void;
  payForJob(payment: InitiatePayment): void;
  resumePayment(jobOfferId: number): void;
  redeemBundle(jobOfferId: number): void;
  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void;
  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void;
  vatDetailsChanged(countryCode: string, vatId: string): void;
  assertUserAuthenticated(): boolean;
  markAsFavourite(jobOfferId: number, favourite: boolean): void;
  apply(jobOffer: JobOffer): void;
  valuePropositionAccepted(
    jobOffer: JobOffer,
    event: ValuePropositionEvent,
    email?: string): void;
}

export interface FilterListener {
  filter(filter: Filter): void;
  filterOnlyMine(onlyMine: boolean): void;
}

export type CanEdit = (jobOfferId: number) => boolean;
export type PricingPlanSelected = () => boolean;
export type TagAutocomplete = (tagPrompt: string, result: TagAutocompleteResult) => void;
export type TagAutocompleteResult = (tags: Tag[]) => void;

export class VueUiFactory {
  public readonly screens: Screens;
  public readonly store: BoardStore;

  private viewListener: ViewListener|null = null;
  private tagAutocomplete: TagAutocomplete|null = null;
  private readonly app;

  constructor(
    private locationInput: LocationInput,
    isAuthenticated: boolean,
    private backendImageApi: BackendImageApi,
    private allJobOffers: JobOfferRepository,
    private planBundle: PlanBundleRepository,
    private filterRepo: FilterRepository,
    private view: View,
  ) {
    this.screens = new Screens(new Policy(
      isAuthenticated,
      (jobOfferId: number): boolean => this.findJobOffer(jobOfferId)?.canEdit ?? false,
      () => this.store.pricingPlan !== null));
    this.app = createApp(JobBoard);
    const pinia = createPinia();
    this.app.use(pinia);
    this.store = useBoardStore();
  }

  selectPlan(plan: PricingPlan): void {
    if (this.viewListener!.assertUserAuthenticated()) {
      this.store.pricingPlan = plan;
      this.screens.navigate('form', null);
    }
  }

  // from main:

  setViewListener(viewListener: ViewListener): void {
    this.viewListener = viewListener;
  }

  setJobOfferFilters(filters: FilterOptions): void {
    this.store.jobOfferFilters = filters;
  }

  setPaymentStatus(status: PaymentStatus): void {
    this.store.paymentStatus = status;
  }

  setTagAutocomplete(tagAutocomplete: TagAutocomplete): void {
    this.tagAutocomplete = tagAutocomplete;
  }

  setJobOfferFavourite(jobOfferId: number, favourite: boolean): void {
    this.findJobOfferReactive(jobOfferId)!.isFavourite = favourite;
  }

  // from view

  setPlanBundle(bundleName: PlanBundleName, remainingJobOffers: number, canRedeem: boolean): void {
    this.store.planBundle = {bundleName, remainingJobOffers, canRedeem};
    this.store.pricingPlan = bundleName;
  }

  mount(element: Element): void {
    this.app.provide(jobBoardServiceInjectKey, new JobBoardService(
      this,
      this.store,
      this.screens,
      this.locationInput,
      this.viewListener!,
      this.tagAutocomplete!,
      this.backendImageApi,
      this.allJobOffers,
      this.planBundle,
      this.filterRepo,
      this.view,
    ));
    this.screens.useIn(this.app);
    this.app.mount(element);
  }

  private findJobOfferReactive(jobOfferId: number): JobOffer {
    const jobOffer = this.store.jobOffers.find(o => o.id === jobOfferId);
    if (jobOffer) {
      return jobOffer;
    }
    const nonReactive = this.findJobOffer(jobOfferId);
    if (nonReactive) {
      return nonReactive;
      // TODO, currently, only offers in list are reactive; 
      //       but offers outside of list (like mine, expired) need to be reactive too
    }
    throw new Error('Failed to render job offer.');
  }

  findJobOffer(jobOfferId: number): JobOffer|null {
    return this.allJobOffers.findJobOffer(jobOfferId);
  }
}

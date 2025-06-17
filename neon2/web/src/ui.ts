import {createPinia} from "pinia";
import {createApp} from 'vue';
import {JobBoardService} from "./neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {BoardStore, useBoardStore} from "./neon3/Apps/VueApp/Modules/JobBoard/store";
import JobBoard from './neon3/Apps/VueApp/Modules/JobBoard/View/JobBoard.vue';
import {jobBoardServiceInjectKey} from "./neon3/Apps/VueApp/Modules/JobBoard/View/vue";
import {LocationInput} from "./neon3/Packages/Core/Application/LocationInput";
import {BackendImageApi} from "./neon3/Packages/Core/Backend/BackendImageApi";
import {FilterRepository} from "./neon3/Packages/Feature/JobBoard/Application/FilterRepository";
import {JobOfferFilterService} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferFilterService";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {PlanBundleRepository} from "./neon3/Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {Tag} from "./neon3/Packages/Feature/JobBoard/Domain/Model";
import {Policy} from "./Policy";
import {Screens} from "./Screens";
import {ViewListener} from "./ViewListener";

export type CanEdit = (jobOfferId: number) => boolean;
export type PricingPlanSelected = () => boolean;

export interface TagAutocomplete {
  (tagPrompt: string, result: TagAutocompleteResult): void;
}

export type TagAutocompleteResult = (tags: Tag[]) => void;

export class VueUiFactory {
  public readonly screens: Screens;
  public readonly store: BoardStore;
  private readonly app;

  private viewListener: ViewListener|null = null;

  constructor(
    private locationInput: LocationInput,
    isAuthenticated: boolean,
    private backendImageApi: BackendImageApi,
    private allJobOffers: JobOfferRepository,
    private planBundle: PlanBundleRepository,
    private filterRepo: FilterRepository,
    private filterService: JobOfferFilterService,
    private tagAutocomplete: TagAutocomplete,
  ) {
    this.screens = new Screens(new Policy(
      isAuthenticated,
      (jobOfferId: number): boolean => this.allJobOffers.findJobOffer(jobOfferId)?.canEdit ?? false,
      () => this.store.pricingPlan !== null));
    this.app = createApp(JobBoard);
    const pinia = createPinia();
    this.app.use(pinia);
    this.store = useBoardStore();
  }

  setViewListener(viewListener: ViewListener): void {
    this.viewListener = viewListener;
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
      this.filterService,
    ));
    this.screens.useIn(this.app);
    this.app.mount(element);
  }

  setJobOfferFavourite(jobOfferId: number, favourite: boolean): void {
    this.findJobOfferReactive(jobOfferId)!.isFavourite = favourite;
  }

  private findJobOfferReactive(jobOfferId: number): JobOffer {
    const jobOffer = this.store.jobOffers.find(o => o.id === jobOfferId);
    if (jobOffer) {
      return jobOffer;
    }
    const nonReactive = this.allJobOffers.findJobOffer(jobOfferId);
    if (nonReactive) {
      return nonReactive;
      // TODO, currently, only offers in list are reactive; 
      //       but offers outside of list (like mine, expired) need to be reactive too
    }
    throw new Error('Failed to render job offer.');
  }
}

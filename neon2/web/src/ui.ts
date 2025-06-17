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
import {TagAutocomplete} from "./neon3/Packages/Feature/JobBoard/Application/TagAutocomplete";
import {Policy} from "./Policy";
import {Screens} from "./Screens";
import {ViewListener} from "./ViewListener";

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
    this.app = createApp(JobBoard);
    const pinia = createPinia();
    this.app.use(pinia);
    this.store = useBoardStore();

    this.screens = new Screens(new Policy(
      isAuthenticated,
      allJobOffers,
      this.store,
    ));
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
}

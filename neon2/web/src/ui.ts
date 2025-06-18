import {createPinia} from "pinia";
import {createApp} from 'vue';
import {JobOfferController} from "./JobOfferController";
import {BoardStore, useBoardStore} from "./neon3/Apps/VueApp/Modules/JobBoard/store";
import JobBoard from './neon3/Apps/VueApp/Modules/JobBoard/View/JobBoard.vue';
import {jobBoardServiceInjectKey} from "./neon3/Apps/VueApp/Modules/JobBoard/View/vue";
import {JobBoardServiceFactory} from "./neon3/Packages/Feature/JobBoard/Application/JobBoardServiceFactory";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {Policy} from "./Policy";
import {Screens} from "./Screens";

export class VueUiFactory {
  public readonly screens: Screens;
  public readonly store: BoardStore;
  private readonly app;

  constructor(
    isAuthenticated: boolean,
    allJobOffers: JobOfferRepository,
    private factory: JobBoardServiceFactory,
  ) {
    this.app = createApp(JobBoard);
    const pinia = createPinia();
    this.app.use(pinia);
    this.store = useBoardStore();
    this.screens = new Screens(new Policy(isAuthenticated, allJobOffers, this.store));
  }

  mount(element: Element, controller: JobOfferController): void {
    this.app.provide(jobBoardServiceInjectKey,
      this.factory.create(controller, this.screens, this.store));
    this.screens.useIn(this.app);
    this.app.mount(element);
  }
}

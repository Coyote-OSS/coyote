import {Screens} from "../../../../../Screens";
import {ViewListener} from "../../../../../ViewListener";
import {JobBoardService} from "../../../../Apps/VueApp/Modules/JobBoard/JobBoardService";
import {BoardStore} from "../../../../Apps/VueApp/Modules/JobBoard/store";
import {LocationInput} from "../../../Core/Application/LocationInput";
import {BackendImageApi} from "../../../Core/Backend/BackendImageApi";
import {FilterRepository} from "./FilterRepository";
import {JobOfferFilterService} from "./JobOfferFilterService";
import {JobOfferRepository} from "./JobOfferRepository";
import {PlanBundleRepository} from "./PlanBundleRepository";
import {TagAutocomplete} from "./TagAutocomplete";

export class JobBoardServiceFactory {
  constructor(
    private locationInput: LocationInput,
    private backendImageApi: BackendImageApi,
    private allJobOffers: JobOfferRepository,
    private planBundle: PlanBundleRepository,
    private filterRepo: FilterRepository,
    private filterService: JobOfferFilterService,
    private tagAutocomplete: TagAutocomplete,
  ) {}

  create(
    viewListener: ViewListener,
    screens: Screens,
    store: BoardStore,
  ): JobBoardService {
    return new JobBoardService(
      store,
      screens,
      this.locationInput,
      viewListener,
      this.tagAutocomplete!,
      this.backendImageApi,
      this.allJobOffers,
      this.planBundle,
      this.filterRepo,
      this.filterService,
    );
  }
}

import {Screens} from "../../../../../Screens";
import {JobBoardService} from "../../../../Apps/VueApp/Modules/JobBoard/JobBoardService";
import {BoardStore} from "../../../../Apps/VueApp/Modules/JobBoard/store";
import {LocationDisplay} from "../../../Core/Application/LocationDisplay";
import {LocationInput} from "../../../Core/Application/LocationInput";
import {BackendImageApi} from "../../../Core/Backend/BackendImageApi";
import {FilterRepository} from "./FilterRepository";
import {JobOfferController} from "./JobOfferController";
import {JobOfferFilterService} from "./JobOfferFilterService";
import {JobOfferRepository} from "./JobOfferRepository";
import {PlanBundleRepository} from "./PlanBundleRepository";
import {TagAutocomplete} from "./TagAutocomplete";

export class JobBoardServiceFactory {
  constructor(
    private locationInput: LocationInput,
    private locationDisplay: LocationDisplay,
    private backendImageApi: BackendImageApi,
    private allJobOffers: JobOfferRepository,
    private planBundle: PlanBundleRepository,
    private filterRepo: FilterRepository,
    private filterService: JobOfferFilterService,
    private tagAutocomplete: TagAutocomplete,
  ) {}

  create(
    screens: Screens,
    store: BoardStore,
    controller: JobOfferController,
  ): JobBoardService {
    return new JobBoardService(
      store,
      screens,
      this.locationInput,
      this.locationDisplay,
      controller,
      this.tagAutocomplete!,
      this.backendImageApi,
      this.allJobOffers,
      this.planBundle,
      this.filterRepo,
      this.filterService);
  }
}

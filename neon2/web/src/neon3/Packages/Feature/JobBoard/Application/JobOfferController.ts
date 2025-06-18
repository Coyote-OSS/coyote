import {ViewModel} from "../../../../Apps/VueApp/Modules/JobBoard/ViewModel";
import {BackendApi} from "../../../Core/Backend/BackendApi";
import {JobBoardBackend} from "../../../Core/Backend/JobBoardBackend";
import {remainingJobOffers} from "../Domain/bundleSize";
import {JobOffer} from "../Domain/JobOffer";
import {PaymentStatus} from "../Domain/Model";
import {FilterRepository} from "./FilterRepository";
import {JobOfferFilterService} from "./JobOfferFilterService";
import {JobOfferRepository} from "./JobOfferRepository";
import {PaymentIntentRepository} from "./PaymentIntentRepository";
import {PlanBundleRepository} from "./PlanBundleRepository";

export class JobOfferController {
  constructor(
    public backend: JobBoardBackend,
    public backendApi: BackendApi,
    private viewModel: ViewModel,
    private paymentIntents: PaymentIntentRepository,
    private planBundleRepo: PlanBundleRepository,
    private jobOffersRepo: JobOfferRepository,
    private filterService: JobOfferFilterService,
    private filterRepo: FilterRepository,
  ) {}

}

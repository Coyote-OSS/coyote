import {JobOfferFilterService} from "./JobOfferFilterService";
import {FilterRepository} from "./neon3/Packages/Feature/JobBoard/Application/FilterRepository";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";

export class View {
  constructor(
    private jobOffers: JobOfferRepository,
    private filterRepo: FilterRepository,
  ) {}

  filterJobOffersReturn(): JobOffer[] {
    if (this.filterRepo.filterOnlyMine) {
      return this.jobOffers.onlyMine();
    }
    const f = new JobOfferFilterService(this.jobOffers, this.filterRepo.filter);
    return f.filterJobOffers();
  }
}

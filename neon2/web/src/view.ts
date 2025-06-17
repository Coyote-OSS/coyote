import {JobOfferFilterService} from "./JobOfferFilterService";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {FilterCriteria} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";

export class View {
  constructor(private jobOffers: JobOfferRepository) {}

  filterJobOffers(criteria: FilterCriteria): JobOffer[] {
    if (criteria.filterOnlyMine) {
      return this.jobOffers.onlyMine();
    }
    const f = new JobOfferFilterService(this.jobOffers, criteria.filter);
    return f.filterJobOffers();
  }
}

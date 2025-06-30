import {JobOffer} from "../../Domain/JobBoard/JobOffer";
import {FilterOptions} from "./filter";
import {JobOfferRepository} from "./JobOfferRepository";

export class JobBoardPresenter {
  constructor(private jobOfferRepo: JobOfferRepository) {}

  filterOptions(): FilterOptions {
    const jobOffers = this.jobOfferRepo.all();
    return {
      locations: [...new Set(jobOffers.flatMap(offer => this.jobOfferCities(offer)))],
      tags: [...new Set(jobOffers.flatMap(offer => this.jobOfferTagNames(offer)))],
    };
  }

  private jobOfferTagNames(jobOffer: JobOffer): string[] {
    return jobOffer.tags.map(tag => tag.tagName);
  }

  private jobOfferCities(jobOffer: JobOffer): string[] {
    return jobOffer.locations
      .map(location => location.city)
      .filter(city => city !== null);
  }
}

import {FilterOptions} from "./neon3/Packages/Feature/JobBoard/Application/filter";
import {SubmitJobOffer} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";

interface JobBoardObserver {
  (jobOffers: JobOffer[]): void;
}

export class JobBoard {
  private readonly jobOffers: JobOffer[];

  constructor(private observe: JobBoardObserver) {
    this.jobOffers = [];
  }

  jobOfferCreated(jobOffer: JobOffer): void {
    this.jobOffers.unshift(jobOffer);
    this.updateView();
  }

  jobOfferUpdated(id: number, jobOffer: SubmitJobOffer): void {
    const originalJobOffer = this.findJobOffer(id);
    Object.assign(originalJobOffer, jobOffer);
    this.updateView();
  }

  private findJobOffer(id: number): JobOffer {
    const jobOffer = this.jobOffers.find(o => o.id === id);
    if (jobOffer) {
      return jobOffer;
    }
    throw new Error('No such job offer.');
  }

  updateView(): void {
    this.observe(copyArray<JobOffer>(this.jobOffers));
  }

  jobOfferPaid(jobOfferId: number): void {
    this.findJobOffer(jobOfferId).status = 'published';
    this.updateView();
  }

  filterOptions(): FilterOptions {
    return {
      locations: [...new Set(this.jobOffers.flatMap(offer => jobOfferCities(offer)))],
      tags: [...new Set(this.jobOffers.flatMap(offer => jobOfferTagNames(offer)))],
    };
  }
}

export function jobOfferTagNames(jobOffer: JobOffer): string[] {
  return jobOffer.tags.map(tag => tag.tagName);
}

export function jobOfferCities(jobOffer: JobOffer): string[] {
  return jobOffer.locations
    .map(location => location.city)
    .filter(city => city !== null);
}

function copyArray<T>(array: T[]): T[] {
  return array.map(object => ({...object}));
}

import {jobOfferCities, jobOfferTagNames} from './jobBoard';
import {Filter} from "./neon3/Packages/Feature/JobBoard/Application/filter";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {sortInPlace} from "./neon3/Packages/Feature/JobBoard/Presenter/orderBy";

export class JobOfferFilterService {
  constructor(
    private jobOffers: JobOfferRepository,
    private filter: Filter,
  ) {}

  filterJobOffers(): JobOffer[] {
    const jobOffers = this.jobOffers.all()
      .filter(jobOffer => jobOffer.status === 'published')
      .filter(jobOffer => this.jobOfferMatches(jobOffer));
    sortInPlace(jobOffers, this.filter.sort);
    return jobOffers;
  }

  private jobOfferMatches(jobOffer: JobOffer): boolean {
    if (!this.jobOfferMatchesSearchPhrase(jobOffer, this.filter.searchPhrase)) {
      return false;
    }
    if (this.filter.workModes.length) {
      if (!this.filter.workModes.includes(jobOffer.workMode)) {
        return false;
      }
    }
    if (this.filter.legalForms.length) {
      if (!this.filter.legalForms.includes(jobOffer.legalForm)) {
        return false;
      }
    }
    if (this.filter.workExperiences.length) {
      if (!this.filter.workExperiences.includes(jobOffer.experience)) {
        return false;
      }
    }
    if (this.filter.tags.length) {
      if (!this.haveCommonElement(this.filter.tags, jobOfferTagNames(jobOffer))) {
        return false;
      }
    }
    if (this.filter.locations.length) {
      if (!this.haveCommonElement(this.filter.locations, jobOfferCities(jobOffer))) {
        return false;
      }
    }
    return true;
  }

  private jobOfferMatchesSearchPhrase(jobOffer: JobOffer, searchPhrase: string): boolean {
    return jobOffer.title.toLowerCase().includes(searchPhrase.toLowerCase())
      || jobOffer.companyName.toLowerCase().includes(searchPhrase.toLowerCase())
      || jobOfferTagNames(jobOffer).some(tagName => tagName.toLowerCase().includes(searchPhrase.toLowerCase()))
      || this.jobOfferDescriptionPlain(jobOffer).toLowerCase().includes(searchPhrase.toLowerCase())
      || this.jobOfferCompanyDescriptionPlain(jobOffer).toLowerCase().includes(searchPhrase.toLowerCase());
  }

  private jobOfferDescriptionPlain(jobOffer: JobOffer): string {
    if (jobOffer.description === null) {
      return '';
    }
    return this.plainText(jobOffer.description);
  }

  private jobOfferCompanyDescriptionPlain(jobOffer: JobOffer): string {
    if (jobOffer.companyDescription === null) {
      return '';
    }
    return this.plainText(jobOffer.companyDescription);
  }

  private plainText(string: string): string {
    return string.replaceAll(new RegExp('</?[a-z]+/?>', 'g'), '');
  }

  private haveCommonElement(array1: string[], array2: string[]): boolean {
    return array1.some(item => array2.includes(item));
  }
}

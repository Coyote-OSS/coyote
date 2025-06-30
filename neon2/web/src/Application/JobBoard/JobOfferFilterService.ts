import {JobOffer} from "../../Domain/JobBoard/JobOffer";
import {Filter} from "./filter";
import {JobOfferRepository} from "./JobOfferRepository";
import {FilterCriteria} from "./Model";
import {sortInPlace} from "./orderBy";

export class JobOfferFilterService {
  constructor(private jobOffers: JobOfferRepository) {}

  filter(criteria: FilterCriteria): JobOffer[] {
    if (criteria.filterOnlyMine) {
      return this.jobOffers.onlyMine();
    }
    if (criteria.filter) {
      return this.filtered(criteria.filter, this.jobOffers.published());
    }
    return this.jobOffers.published();
  }

  private filtered(filter: Filter, jobOffers: JobOffer[]): JobOffer[] {
    const filtered = jobOffers.filter(jobOffer => this.jobOfferMatches(filter, jobOffer));
    sortInPlace(filtered, filter.sort);
    return filtered;
  }

  private jobOfferMatches(filter: Filter, jobOffer: JobOffer): boolean {
    if (!this.jobOfferMatchesSearchPhrase(jobOffer, filter.searchPhrase)) {
      return false;
    }
    if (filter.workModes.length) {
      if (!filter.workModes.includes(jobOffer.workMode)) {
        return false;
      }
    }
    if (filter.legalForms.length) {
      if (!filter.legalForms.includes(jobOffer.legalForm)) {
        return false;
      }
    }
    if (filter.workExperiences.length) {
      if (!filter.workExperiences.includes(jobOffer.experience)) {
        return false;
      }
    }
    if (filter.tags.length) {
      if (!this.haveCommonElement(filter.tags, jobOfferTagNames(jobOffer))) {
        return false;
      }
    }
    if (filter.locations.length) {
      if (!this.haveCommonElement(filter.locations, jobOfferCities(jobOffer))) {
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

function jobOfferTagNames(jobOffer: JobOffer): string[] {
  return jobOffer.tags.map(tag => tag.tagName);
}

function jobOfferCities(jobOffer: JobOffer): string[] {
  return jobOffer.locations
    .map(location => location.city)
    .filter(city => city !== null);
}

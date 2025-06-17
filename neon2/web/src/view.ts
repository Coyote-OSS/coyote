import {jobOfferCities, jobOfferTagNames} from './jobBoard';
import {AllJobOffers} from "./neon3/Packages/Feature/JobBoard/Application/AllJobOffers";
import {Filter} from "./neon3/Packages/Feature/JobBoard/Application/filter";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {sortInPlace} from "./neon3/Packages/Feature/JobBoard/Presenter/orderBy";
import {VueUiFactory} from './ui';

export class View {
  private filter: Filter|null = null;
  private filterOnlyMine: boolean = false;
  private filterListener: FilterListener|null = null;

  constructor(
    private ui: VueUiFactory,
    private allJobOffers: AllJobOffers,
  ) {
    this.ui.addFilterListener({
      filter: (filter: Filter): void => {
        this.filter = filter;
        this.filterListener!.filterChange(filter);
        this.filterJobOffers();
      },
      filterOnlyMine: (onlyMine: boolean): void => {
        this.filterOnlyMine = onlyMine;
        this.filterJobOffers();
      },
    });
  }

  filterJobOffers(): void {
    if (this.filterOnlyMine) {
      const jobOffers = this.allJobOffers.all().filter(jobOffer => jobOffer.isMine);
      jobOffers.sort();
      this.ui.setJobOffers(jobOffers);
      return;
    }
    const jobOffers = this.allJobOffers.all()
      .filter(jobOffer => jobOffer.status === 'published')
      .filter(jobOffer => this.jobOfferMatches(jobOffer));
    if (this.filter) {
      sortInPlace(jobOffers, this.filter.sort);
    }
    this.ui.setJobOffers(jobOffers);
  }

  private jobOfferMatches(jobOffer: JobOffer): boolean {
    if (this.filter === null) {
      return true;
    }
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

  addFilterListener(listener: FilterListener): void {
    this.filterListener = listener;
  }
}

export interface FilterListener {
  filterChange(filter: Filter): void;
}

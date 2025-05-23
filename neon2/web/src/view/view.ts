import {JobOffer, jobOfferCities, jobOfferTagNames} from '../jobBoard';
import {JobOfferFilter, sortInPlace} from "../jobOfferFilter";
import {PlanBundleName} from "../main";
import {Screen, VueUi} from './ui/ui';

export type Toast = 'created'|'edited'|'bundle-used';

export class View {
  private jobOffers: JobOffer[] = [];
  private filter: JobOfferFilter|null = null;
  private filterOnlyMine: boolean = false;
  private planBundleCanRedeem: boolean = false;
  private filterListener: FilterListener|null = null;

  constructor(private ui: VueUi) {
    this.ui.setNavigationListener({
      setScreen(screen: Screen, jobOfferId: number|null): void {
        ui.setToast(null);
        ui.setScreen(screen, jobOfferId);
      },
      showJobOfferForm: (): void => {
        if (this.planBundleCanRedeem) {
          this.ui.setScreen('form', null);
        } else {
          this.ui.setScreen('pricing', null);
        }
      },
    });
    this.ui.addFilterListener({
      filter: (filter: JobOfferFilter): void => {
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

  setJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffers = jobOffers;
    this.filterJobOffers();
  }

  private filterJobOffers(): void {
    if (this.filterOnlyMine) {
      const jobOffers = this.jobOffers.filter(jobOffer => jobOffer.isMine);
      jobOffers.sort();
      this.ui.setJobOffers(jobOffers);
      return;
    }
    const jobOffers = this.jobOffers
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

  jobOfferCreatedFree(jobOfferId: number): void {
    this.ui.setToast('created');
    this.ui.setScreen('home', null);
  }

  jobOfferCreatedRequirePayment(jobOfferId: number): void {
    this.ui.setToast('created');
    this.ui.setScreen('payment', jobOfferId);
  }

  jobOfferEdited(jobOfferId: number): void {
    this.ui.setToast('edited');
    this.ui.setScreen('home', null);
  }

  planBundleUsed(): void {
    this.ui.setToast('bundle-used');
  }

  jobOfferPaid(): void {
    this.ui.setScreen('home', null);
  }

  setPlanBundle(planName: PlanBundleName, remainingJobOffers: number): void {
    this.planBundleCanRedeem = remainingJobOffers > 0;
    this.ui.setPlanBundle(planName, remainingJobOffers, this.planBundleCanRedeem);
  }

  addFilterListener(listener: FilterListener): void {
    this.filterListener = listener;
  }
}

export interface FilterListener {
  filterChange(filter: JobOfferFilter): void;
}

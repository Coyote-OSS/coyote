import {JobOffer} from '../jobBoard';
import {UserInterface, ViewListener} from './ui/ui';

export type Toast = 'created'|'edited';

export class View {
  private jobOffers: JobOffer[] = [];
  private searchPhrase: string = '';

  constructor(private ui: UserInterface) {
    this.ui.addNavigationListener((): void => {
      this.ui.setToast(null);
    });
    this.ui.addSearchListener(searchPhrase => {
      this.searchPhrase = searchPhrase;
      this.filterJobOffers();
    });
  }

  addEventListener(listener: ViewListener): void {
    this.ui.addViewListener(listener);
  }

  mount(cssSelector: string): void {
    this.ui.mount(cssSelector);
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffers = jobOffers;
    this.filterJobOffers();
  }

  private filterJobOffers(): void {
    this.ui.setJobOffers(this.jobOffers.filter(jobOffer => this.jobOfferMatches(jobOffer)));
  }

  private jobOfferMatches(jobOffer: JobOffer): boolean {
    return jobOffer.title.toLowerCase().includes(this.searchPhrase.toLowerCase());
  }

  toastCreated(): void {
    this.ui.setToast('created');
  }

  toastEdited(): void {
    this.ui.setToast('edited');
  }
}

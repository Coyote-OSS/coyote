import {JobOffer} from '../jobBoard';
import {Screen, UserInterface, ViewListener} from './ui/ui';

export type Toast = 'created'|'edited';

export class View {
  private jobOffers: JobOffer[] = [];
  private searchPhrase: string = '';

  constructor(private ui: UserInterface) {
    this.ui.addNavigationListener((screen: Screen): void => {
      this.ui.setToast(null);
      this.ui.setScreen(screen);
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

  jobOfferCreated(plan: 'free'|'paid'): void {
    this.ui.setToast('created');
    if (plan === 'free') {
      this.ui.setScreen('home');
    } else {
      this.ui.setScreen('payment');
    }
  }

  jobOfferEdited(): void {
    this.ui.setToast('edited');
    this.ui.setScreen('home');
  }
}

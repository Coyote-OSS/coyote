import {JobOffer} from '../jobBoard';
import {UserInterface, ViewListener} from './ui/ui';

export type Toast = 'created'|'edited';

export class View {
  constructor(private ui: UserInterface) {
    this.ui.addNavigationListener((): void => {
      this.ui.setToast(null);
    });
  }

  addEventListener(listener: ViewListener): void {
    this.ui.addViewListener(listener);
  }

  mount(cssSelector: string): void {
    this.ui.mount(cssSelector);
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.ui.setJobOffers(jobOffers);
  }

  toastCreated(): void {
    this.ui.setToast('created');
  }

  toastEdited(): void {
    this.ui.setToast('edited');
  }
}

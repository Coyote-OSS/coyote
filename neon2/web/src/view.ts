import {JobOffer} from './jobBoard';

export class View {
  private currentlyEditedJobOffer: number|null = null;

  constructor(private events: ViewEvents) {
  }

  mount(): void {
    window.addEventListener('load', () => {
      document.getElementById('add')!
        .addEventListener('click', () => this.events.onJobCreate(this.jobOfferTitle()));
      document.getElementById('save')!
        .addEventListener('click', () => this.finishEditing(this.jobOfferTitle()));
    });
  }

  private jobOfferTitle(): string {
    const jobOfferTitle = document.querySelector('#jobOfferTitle') as HTMLInputElement;
    return jobOfferTitle.value;
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.clearJobOffers();
    for (const jobOffer of jobOffers) {
      this.addJobOffer(jobOffer);
    }
  }

  private clearJobOffers(): void {
    document.querySelector('#jobOffers')!.innerHTML = '';
  }

  private addJobOffer(jobOffer: JobOffer): void {
    const listItem = document.createElement('li');
    listItem.textContent = jobOffer.title;
    listItem.dataset.testid = 'jobOfferTitle';
    listItem.addEventListener('click', () => {
      this.currentlyEditedJobOffer = jobOffer.id;
    });
    document.querySelector('#jobOffers')!.appendChild(listItem);
  }

  private finishEditing(jobOfferTitle: string): void {
    this.events.onJobUpdate(this.currentlyEditedJobOffer, jobOfferTitle);
  }
}

interface ViewEvents {
  onJobCreate: (title: string) => void;
  onJobUpdate: (id: number, title: string) => void;
}

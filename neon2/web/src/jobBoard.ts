interface JobBoardObserver {
  (jobOffers: JobOffer[]): void;
}

type ToastListener = (toast: Toast) => void;

export class JobBoard {
  private readonly jobOffers: JobOffer[];
  private readonly toastListeners: ToastListener[] = [];

  constructor(private observe: JobBoardObserver) {
    this.jobOffers = [];
  }

  count(): number {
    return this.jobOffers.length;
  }

  jobOfferCreated(jobOffer: JobOffer): void {
    this.jobOffers.push(jobOffer);
    this.updateView();
    this.toastListeners.forEach(listener => listener('created'));
  }

  jobOfferUpdated(id: number, targetTitle: string): void {
    this.findJobOffer(id).title = targetTitle;
    this.updateView();
    this.toastListeners.forEach(listener => listener('edited'));
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

  onToast(listener: ToastListener): void {
    this.toastListeners.push(listener);
  }
}

export type Toast = 'created'|'edited';

export interface JobOffer {
  id: number;
  title: string;
  expiresInDays: number;
}

function copyArray<T>(array: T[]): T[] {
  return array.map(object => ({...object}));
}

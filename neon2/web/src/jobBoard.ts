interface JobBoardObserver {
  (jobOffers: JobOffer[], jobOffers: JobOffer[]): void;
}

export class JobBoard {
  private readonly jobOffers: JobOffer[];

  constructor(private observe: JobBoardObserver) {
    this.jobOffers = [];
  }

  count(): number {
    return this.jobOffers.length;
  }

  jobOfferCreated(jobOffer: JobOffer): void {
    this.jobOffers.push(jobOffer);
    this.updateView();
  }

  jobOfferUpdated(id: number, targetTitle: string): void {
    this.findJobOffer(id).title = targetTitle;
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
    const publishedJobOffers = copyArray<JobOffer>(this.jobOffers.filter(offer => offer.status === 'published'));
    this.observe(publishedJobOffers, this.jobOffers);
  }

  jobOfferPaid(jobOfferId: number): void {
    this.findJobOffer(jobOfferId).status = 'published';
    this.updateView();
  }
}

export interface JobOffer {
  id: number;
  title: string;
  expiresInDays: number;
  status: 'published'|'awaitingPayment';
}

function copyArray<T>(array: T[]): T[] {
  return array.map(object => ({...object}));
}

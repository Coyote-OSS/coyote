interface JobBoardObserver {
  (jobOffers: JobOffer[]): void;
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

  jobOfferUpdated(id: number, targetTitle: string, targetDescription: string, targetCompanyName: string): void {
    const jobOffer = this.findJobOffer(id);
    jobOffer.title = targetTitle;
    jobOffer.description = targetDescription;
    jobOffer.companyName = targetCompanyName;
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
    this.observe(copyArray<JobOffer>(this.jobOffers.filter(offer => offer.status === 'published')));
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
  description: string;
  companyName: string;
}

function copyArray<T>(array: T[]): T[] {
  return array.map(object => ({...object}));
}

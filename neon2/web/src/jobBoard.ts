interface JobBoardObserver {
  (jobOffers: JobOffer[]): void;
}

export class JobBoard {
  private readonly jobOffers: JobOffer[];
  private autoInc = 1;

  constructor(private observe: JobBoardObserver) {
    this.jobOffers = [];
  }

  count(): number {
    return this.jobOffers.length;
  }

  addJobOffer(title: string, pricingPlan: 'free'|'paid'): void {
    this.jobOffers.push({
      title,
      id: this.autoInc++,
      expiresInDays: pricingPlan === 'free' ? 14 : 30,
    });
    this.updateView();
  }

  updateJobOffer(id: number, targetTitle: string): void {
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
    this.observe(copyArray<JobOffer>(this.jobOffers));
  }
}

export interface JobOffer {
  id: number;
  title: string;
  expiresInDays: number;
}

function copyArray<T>(array: T[]): T[] {
  return array.map(copy);
}

function copy<T>(object: T): T {
  return {...object};
}

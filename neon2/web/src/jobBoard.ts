import {SubmitJobOffer} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";

interface JobBoardObserver {
  (jobOffers: JobOffer[]): void;
}

export class JobBoard {
  private readonly jobOffers: JobOffer[];

  constructor(private observe: JobBoardObserver) {
    this.jobOffers = [];
  }

  init(jobOffers: JobOffer[]): void {
    this.jobOffers.push(...jobOffers);
    this.updateView();
  }

  jobOfferCreated(jobOffer: JobOffer): void {
    this.jobOffers.unshift(jobOffer);
    this.updateView();
  }

  jobOfferUpdated(id: number, jobOffer: SubmitJobOffer): void {
    const originalJobOffer = this.findJobOffer(id);
    Object.assign(originalJobOffer, jobOffer);
    this.updateView();
  }

  jobOfferPaid(jobOfferId: number): void {
    this.findJobOffer(jobOfferId).status = 'published';
    this.updateView();
  }

  updateView(): void {
    this.observe(copyArray<JobOffer>(this.jobOffers));
  }

  private findJobOffer(id: number): JobOffer {
    const jobOffer = this.jobOffers.find(o => o.id === id);
    if (jobOffer) {
      return jobOffer;
    }
    throw new Error('No such job offer.');
  }
}

function copyArray<T>(array: T[]): T[] {
  return array.map(object => ({...object}));
}

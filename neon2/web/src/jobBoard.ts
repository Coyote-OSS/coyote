import {Currency, LegalForm, Rate, SubmitJobOffer, WorkExperience, WorkMode} from "./main";

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

  jobOfferUpdated(id: number, jobOffer: SubmitJobOffer): void {
    const originalJobOffer = this.findJobOffer(id);
    Object.assign(originalJobOffer, jobOffer);
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
  expiresInDays: number;
  status: 'published'|'awaitingPayment';
  isNew: boolean;
  isFavourite: boolean;
  title: string;
  description: string;
  companyName: string;
  salaryRangeFrom: number;
  salaryRangeTo: number;
  salaryIsNet: boolean;
  salaryCurrency: Currency;
  salaryRate: Rate;
  locations: string[];
  companyLogoUrl: string;
  tagNames: string[];
  workMode: WorkMode;
  legalForm: LegalForm;
  experience: WorkExperience;
}

function copyArray<T>(array: T[]): T[] {
  return array.map(object => ({...object}));
}

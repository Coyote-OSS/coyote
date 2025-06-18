import {JobOffer} from "../Domain/JobOffer";
import {SubmitJobOffer} from "./Model";

export class JobOfferRepository {
  public jobOffers: JobOffer[] = [];

  setJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffers = jobOffers;
  }

  insertFirst(jobOffer: JobOffer): void {
    this.jobOffers.unshift(jobOffer);
  }

  updateJobOfferPublished(jobOfferId: number): void {
    this.findJobOffer(jobOfferId)!.status = 'published';
  }

  updateJobOffer(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    const originalJobOffer = this.findJobOffer(jobOfferId)!;
    Object.assign(originalJobOffer, jobOffer);
  }

  all(): JobOffer[] {
    return this.jobOffers;
  }

  published(): JobOffer[] {
    return this.jobOffers.filter(jobOffer => jobOffer.status === 'published');
  }

  onlyMine(): JobOffer[] {
    return this.jobOffers.filter(jobOffer => jobOffer.isMine);
  }

  findJobOffer(jobOfferId: number): JobOffer|null {
    const jobOffer = this.jobOffers.find(o => o.id === jobOfferId);
    if (jobOffer) {
      return jobOffer;
    }
    return null;
  }
}

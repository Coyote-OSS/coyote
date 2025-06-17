import {JobOffer} from "../Domain/JobOffer";

export class JobOfferRepository {
  public jobOffers: JobOffer[] = [];

  setJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffers = jobOffers;
  }

  all(): JobOffer[] {
    return this.jobOffers;
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

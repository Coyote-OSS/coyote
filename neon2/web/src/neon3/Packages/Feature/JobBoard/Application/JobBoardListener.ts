import {JobOffer} from "../Domain/JobOffer";

export interface JobBoardListener {
  notifyJobOffersChanged(jobOffers: JobOffer[]): void;
}

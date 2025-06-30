import {JobOffer} from "../../../Domain/JobBoard/JobOffer";

export interface JobBoardListener {
  notifyJobOffersChanged(jobOffers: JobOffer[]): void;
}

import {BoardStore} from "../../../../Apps/VueApp/Modules/JobBoard/store";
import {JobOfferRepository} from "./JobOfferRepository";

export class Policy {
  constructor(
    private isAuthenticated: boolean,
    private jobOffers: JobOfferRepository,
    private store: BoardStore,
  ) {}

  createJobOffer(): boolean {
    return this.isAuthenticated && this.pricingPlanSelected();
  }

  private pricingPlanSelected(): boolean {
    return this.store.pricingPlan !== null;
  }

  editJobOffer(jobOfferId: number): boolean {
    return this.isAuthenticated && this.canEditJobOffer(jobOfferId);
  }

  private canEditJobOffer(jobOfferId: number) {
    return this.jobOffers.findJobOffer(jobOfferId)?.canEdit ?? false;
  }
}

import {BoardStore} from "./neon3/Apps/VueApp/Modules/JobBoard/store";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";

export class Policy {
  constructor(
    private isAuthenticated: boolean,
    private allJobOffers: JobOfferRepository,
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
    return this.allJobOffers.findJobOffer(jobOfferId)?.canEdit ?? false;
  }
}

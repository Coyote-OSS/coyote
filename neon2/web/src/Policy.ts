import {CanEdit, PricingPlanSelected} from "./ui";

export class Policy {
  constructor(
    private isAuthenticated: boolean,
    private canEdit: CanEdit,
    private pricingPlanSelected: PricingPlanSelected,
  ) {}

  createCreateJobOffer(): boolean {
    return this.isAuthenticated && this.pricingPlanSelected();
  }

  canEditJobOffer(jobOfferId: number): boolean {
    return this.isAuthenticated && this.canEdit(jobOfferId);
  }
}

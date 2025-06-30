import {BoardStore} from "./boardStore";
import {PricingPlanPort} from "../../../Application/JobBoard/Port/PricingPlanPort";

export class BoardStorePricingPlanAdapter implements PricingPlanPort {
  constructor(private store: BoardStore) {}

  pricingPlanSelected(): boolean {
    return this.store.pricingPlan !== null;
  }
}

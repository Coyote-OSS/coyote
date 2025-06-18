import {ViewModel} from "../../../../Apps/VueApp/Modules/JobBoard/ViewModel";
import {PlanBundleName} from "../Domain/Model";

export class PlanBundleListenerAdapter {
  constructor(private viewModel: ViewModel) {}

  notify(plan: PlanBundleName, remainingJobOffers: number): void {
    this.viewModel.notifyPlanBundleChanged(plan, remainingJobOffers, remainingJobOffers > 0);
  }
}

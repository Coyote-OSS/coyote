import {JobBoardView} from "./JobBoardView";
import {PlanBundleName} from "../../../Domain/JobBoard/JobBoard";

export class PlanBundleListenerAdapter {
  constructor(private viewModel: JobBoardView) {}

  notify(plan: PlanBundleName, remainingJobOffers: number): void {
    this.viewModel.notifyPlanBundleChanged(plan, remainingJobOffers, remainingJobOffers > 0);
  }
}

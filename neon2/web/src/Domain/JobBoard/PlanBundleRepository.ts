import {PlanBundleName} from "./JobBoard";
import {PlanBundle} from "./PlanBundle";

export class PlanBundleRepository {
  private listeners: PlanBundleListener[] = [];
  private plan: PlanBundleName|null = null;
  private remainingJobOffers: number|null = null;

  addListener(listener: PlanBundleListener): void {
    this.listeners.push(listener);
  }

  initPlanBundle(bundle: PlanBundle): void {
    if (bundle.hasBundle) {
      this.set(bundle.planBundleName!, bundle.remainingJobOffers!);
    }
  }

  set(plan: PlanBundleName, remainingJobOffers: number): void {
    this.plan = plan;
    this.remainingJobOffers = remainingJobOffers;
    this.updateListeners();
  }

  decrease(): void {
    if (this.remainingJobOffers === null) {
      throw new Error('Failed to decrease a plan bundle, that was not set.');
    }
    this.remainingJobOffers -= 1;
    this.updateListeners();
  }

  canRedeem(): boolean {
    if (this.plan === null) {
      return false;
    }
    return this.remainingJobOffers! > 0;
  }

  private updateListeners(): void {
    this.listeners.forEach(listener => listener.notify(this.plan!, this.remainingJobOffers!));
  }
}

export interface PlanBundleListener {
  notify(plan: PlanBundleName, remainingJobOffers: number): void;
}

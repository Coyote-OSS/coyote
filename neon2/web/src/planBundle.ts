import {PlanBundleName} from "./neon3/Packages/Feature/JobBoard/Domain/Model";

export class PlanBundle {
  private listeners: PlanBundleListener[] = [];
  private plan: PlanBundleName|null = null;
  private remainingJobOffers: number|null = null;

  addListener(listener: PlanBundleListener): void {
    this.listeners.push(listener);
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

  private updateListeners(): void {
    this.listeners.forEach(listener => listener(this.plan!, this.remainingJobOffers!));
  }
}

type PlanBundleListener = (plan: PlanBundleName, remainingJobOffers: number) => void;

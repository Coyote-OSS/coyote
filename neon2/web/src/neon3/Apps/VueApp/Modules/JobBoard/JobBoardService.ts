import {Screen, UiController, ViewListener} from "../../../../../view/ui/ui";
import {SubmitJobOffer} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {BoardStore} from "./store";

export class JobBoardService {
  constructor(
    private store: BoardStore,
    private viewListener: ViewListener,
    private uiController: UiController,
  ) {}

  redeemBundle(jobOfferId: number): void {
    this.viewListener.redeemBundle(jobOfferId);
  }

  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    this.viewListener.updateJob(jobOfferId, jobOffer);
  }

  showJob(
    jobOfferId: number,
    /** @deprecated **/
    jobOffer: JobOffer,
  ): void {
    this.uiController.showJobOffer(jobOffer!);
  }

  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void {
    this.viewListener.mountLocationDisplay(element, latitude, longitude);
  }

  navigate(screen: Screen, jobOfferId: number|null): void {
    this.uiController.navigate(screen, jobOfferId);
  }

  applyForJob(jobOfferId: number): void {
    this.uiController.applyForJob(jobOfferId);
  }

  findJobOffer(jobOfferId: number): JobOffer|null {
    return this.uiController.findJobOffer(jobOfferId);
  }

  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    this.uiController.markAsFavourite(jobOfferId, favourite);
  }

  selectPlan(plan: PricingPlan): void {
    this.uiController.selectPlan(plan);
  }
}

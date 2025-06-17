import {PaymentNotification} from "../../neon3/Packages/Core/Application/PaymentProvider";
import {JobOffer} from "../../neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {PaymentStatus} from "../../neon3/Packages/Feature/JobBoard/Domain/Model";
import {Toast} from "../view";
import {Screen} from "./ui";

export interface JobBoardProperties {
  screen: Screen;
  toast: Toast|null;
  paymentNotification: PaymentNotification|null;
  paymentStatus: PaymentStatus|null;
  vpVisibleFor: JobOffer|null;
}

import {ValuePropositionEvent} from "./main";
import {InitiatePayment, SubmitJobOffer} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {PricingPlan} from "./neon3/Packages/Feature/JobBoard/Domain/Model";

export interface ViewListener {
  createJob(plan: PricingPlan, jobOffer: SubmitJobOffer): void;
  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void;
  payForJob(payment: InitiatePayment): void;
  resumePayment(jobOfferId: number): void;
  redeemBundle(jobOfferId: number): void;
  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void;
  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void;
  vatDetailsChanged(countryCode: string, vatId: string): void;
  assertUserAuthenticated(): boolean;
  markAsFavourite(jobOfferId: number, favourite: boolean): void;
  apply(jobOffer: JobOffer): void;
  valuePropositionAccepted(
    jobOffer: JobOffer,
    event: ValuePropositionEvent,
    email?: string): void;
}

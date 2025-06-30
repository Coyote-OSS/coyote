import {InvoiceInformation, PlanBundleName} from "../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../Domain/JobBoard/JobOffer";
import {Filter} from "./filter";
import {PaymentMethod} from "./Port/PaymentProvider";
import {SubmitJobOffer} from "./Port/SubmitJobOffer";

export function toSubmitJobOffer(jobOffer: JobOffer): SubmitJobOffer {
  return jobOffer;
}

export interface InitiatePayment {
  jobOfferId: number;
  invoiceInfo: InvoiceInformation;
  paymentMethod: PaymentMethod;
}

export interface PlanBundle {
  bundleName: PlanBundleName;
  remainingJobOffers: number;
  canRedeem: boolean;
}

export interface FilterCriteria {
  filterOnlyMine: boolean;
  filter: Filter|null;
}

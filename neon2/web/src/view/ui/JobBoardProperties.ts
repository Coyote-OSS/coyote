import {JobOfferFilter} from "../../jobOfferFilter";
import {JobOfferFilters, VatIdState} from "../../main";
import {LocationInput} from "../../neon3/Packages/Core/Application/LocationInput";
import {PaymentNotification} from "../../neon3/Packages/Core/Application/PaymentProvider";
import {PlanBundle} from "../../neon3/Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {Country, PaymentStatus, PricingPlan} from "../../neon3/Packages/Feature/JobBoard/Domain/Model";
import {PaymentSummary} from "../../neon3/Packages/Feature/JobBoard/Presenter/Model";
import {Toast} from "../view";
import {Screen} from "./ui";

export interface JobBoardProperties {
  jobOffers: JobOffer[];
  jobOfferFilter: JobOfferFilter;
  jobOfferFilters: JobOfferFilters;
  screen: Screen;
  toast: Toast|null;
  paymentNotification: PaymentNotification|null;
  paymentStatus: PaymentStatus|null;
  planBundle: PlanBundle|null;
  pricingPlan: PricingPlan|null;
  applicationEmail: string|null;
  paymentSummary: PaymentSummary|null;
  paymentVatIdState: VatIdState;
  invoiceCountries: Country[]|null;
  locationInput: LocationInput|null;
  paymentProcessing: boolean;
  vpVisibleFor: JobOffer|null;
}

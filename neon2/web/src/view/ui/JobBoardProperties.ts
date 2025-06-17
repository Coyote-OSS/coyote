import {JobOfferFilter} from "../../jobOfferFilter";
import {Country, JobOfferFilters, PaymentSummary, PricingPlan, UploadAssets, VatIdState} from "../../main";
import {LocationInput} from "../../neon3/Core/Application/LocationInput";
import {PaymentNotification} from "../../neon3/Core/Application/PaymentProvider";
import {JobOffer} from "../../neon3/Feature/JobBoard/Model/JobOffer";
import {PaymentStatus} from "../../paymentProvider/PaymentService";
import {Toast} from "../view";
import {PlanBundle, Screen, TagAutocomplete, UiController, ViewListener} from "./ui";

export interface JobBoardProperties {
  viewListener: ViewListener|null;
  tagAutocomplete: TagAutocomplete|null;
  jobOffers: JobOffer[];
  jobOfferFilter: JobOfferFilter;
  jobOfferFilters: JobOfferFilters;
  screen: Screen;
  toast: Toast|null;
  paymentNotification: PaymentNotification|null;
  paymentStatus: PaymentStatus|null;
  planBundle: PlanBundle|null;
  pricingPlan: PricingPlan|null;
  upload: UploadAssets|null;
  applicationEmail: string|null;
  paymentSummary: PaymentSummary|null;
  paymentVatIdState: VatIdState;
  invoiceCountries: Country[]|null;
  locationInput: LocationInput|null;
  uiController: UiController;
  paymentProcessing: boolean;
  vpVisibleFor: JobOffer|null;
}

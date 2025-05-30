import {JobOffer} from "../../jobBoard";
import {JobOfferFilter} from "../../jobOfferFilter";
import {LocationInput} from "../../location/LocationInput";
import {Country, JobOfferFilters, PaymentSummary, PricingPlan, UploadAssets, VatIdState} from "../../main";
import {PaymentNotification} from "../../paymentProvider/PaymentProvider";
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
}

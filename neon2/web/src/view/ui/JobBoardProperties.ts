import {JobOfferFilter} from "../../jobOfferFilter";
import {Country, JobOfferFilters, UploadAssets, VatIdState} from "../../main";
import {LocationInput} from "../../neon3/Packages/Core/Application/LocationInput";
import {PaymentNotification} from "../../neon3/Packages/Core/Application/PaymentProvider";
import {JobOffer} from "../../neon3/Packages/Feature/JobBoard/Application/JobOffer";
import {PricingPlan} from "../../neon3/Packages/Feature/JobBoard/Domain/Model";
import {PaymentSummary} from "../../neon3/Packages/Feature/JobBoard/Presenter/Model";
import {PaymentStatus} from "../../PaymentService";
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

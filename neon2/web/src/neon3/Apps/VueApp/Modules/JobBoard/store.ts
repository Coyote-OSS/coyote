import {defineStore} from 'pinia';
import {VatIdState} from "../../../../../main";
import {emptyJobOfferFilter} from "./View/component/JobOfferFilters";
import {PaymentNotification} from "../../../../Packages/Core/Application/PaymentProvider";
import {Filter, FilterOptions} from "../../../../Packages/Feature/JobBoard/Application/filter";
import {PlanBundle} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {Country, PaymentStatus, PaymentSummary, PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {Screen, Toast} from "../../../../Packages/Feature/JobBoard/Presenter/Model";

export const useBoardStore = defineStore('jobBoard', {
  state(): State {
    return {
      // layout
      toast: null,
      screen: 'home',
      paymentNotification: null,
      paymentStatus: null,
      vpVisibleFor: null,

      // search
      jobOffers: [],
      jobOfferFilters: {
        tags: [],
        locations: [],
      },
      jobOfferFilter: emptyJobOfferFilter(),

      // create job offer
      applicationEmail: null,
      pricingPlan: null,

      // payment
      planBundle: null,
      paymentSummary: null,
      paymentVatIdState: 'valid',
      invoiceCountries: null,
      paymentProcessing: false,
    };
  },
});

interface State {
  // layout
  screen: Screen;
  toast: Toast|null;
  paymentNotification: PaymentNotification|null;
  paymentStatus: PaymentStatus|null;
  vpVisibleFor: JobOffer|null;

  // search
  jobOffers: JobOffer[];
  jobOfferFilter: Filter;
  jobOfferFilters: FilterOptions;

  // create job offer
  pricingPlan: PricingPlan|null;
  applicationEmail: string|null;

  // payment
  planBundle: PlanBundle|null;
  paymentSummary: PaymentSummary|null;
  paymentVatIdState: VatIdState;
  invoiceCountries: Country[]|null;
  paymentProcessing: boolean;
}

export type BoardStore = ReturnType<typeof useBoardStore>;

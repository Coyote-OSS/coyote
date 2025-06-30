import {defineStore} from 'pinia';
import {VatIdState} from "../../../Application/JobBoard/Port/PaymentListener";
import {emptyJobOfferFilter} from "./View/component/JobOfferFilters";
import {PaymentNotification} from "../../../Application/JobBoard/Port/PaymentProvider";
import {Filter, FilterOptions} from "../../../Application/JobBoard/filter";
import {PlanBundle} from "../../../Application/JobBoard/Model";
import {JobOffer} from "../../../Domain/JobBoard/JobOffer";
import {Country, PaymentUpdatedStatus, PaymentSummary, PricingPlan} from "../../../Domain/JobBoard/JobBoard";
import {ScreenName, Toast} from "./Model";

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
  screen: ScreenName;
  toast: Toast|null;
  paymentNotification: PaymentNotification|null;
  paymentStatus: PaymentUpdatedStatus|null;
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

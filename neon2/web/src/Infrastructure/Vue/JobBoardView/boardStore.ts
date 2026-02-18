import {defineStore} from 'pinia';
import {Filter, FilterOptions} from "../../../Application/JobBoard/filter";
import {PlanBundle} from "../../../Application/JobBoard/Model";
import {VatIdState} from "../../../Application/JobBoard/Port/PaymentListener";
import {PaymentNotification} from "../../../Application/JobBoard/Port/PaymentProvider";
import {Country, PaymentSummary, PaymentUpdatedStatus, PricingPlan} from "../../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../../Domain/JobBoard/JobOffer";
import {Toast} from "./Model";
import {emptyJobOfferFilter} from "./View/component/JobOfferFilters";

export const useBoardStore = defineStore('jobBoard', {
  state(): State {
    return {
      // layout
      toast: null,
      paymentNotification: null,
      paymentStatus: null,

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
  toast: Toast|null;
  paymentNotification: PaymentNotification|null;
  paymentStatus: PaymentUpdatedStatus|null;

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

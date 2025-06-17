import {defineStore} from 'pinia';
import {VatIdState} from "../../../../../main";
import {emptyJobOfferFilter} from "../../../../../view/ui/JobOffer/JobOfferFilters";
import {Filter, FilterOptions} from "../../../../Packages/Feature/JobBoard/Application/filter";
import {PlanBundle} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {Country, PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {PaymentSummary} from "../../../../Packages/Feature/JobBoard/Presenter/Model";

export const useBoardStore = defineStore('jobBoard', {
  state(): State {
    return {
      applicationEmail: null,
      pricingPlan: null,
      jobOffers: [],
      jobOfferFilters: {
        tags: [],
        locations: [],
      },
      jobOfferFilter: emptyJobOfferFilter(),

      planBundle: null,
      paymentSummary: null,
      paymentVatIdState: 'valid',
      invoiceCountries: null,
      paymentProcessing: false,
    };
  },
});

interface State {
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

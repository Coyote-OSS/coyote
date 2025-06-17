import {defineStore} from 'pinia';
import {emptyJobOfferFilter} from "../../../../../view/ui/JobOffer/JobOfferFilters";
import {Filter, FilterOptions} from "../../../../Packages/Feature/JobBoard/Application/filter";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";

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
    };
  },
});

interface State {
  jobOffers: JobOffer[];
  jobOfferFilter: Filter;
  jobOfferFilters: FilterOptions;
  applicationEmail: string|null;
  pricingPlan: PricingPlan|null;
}

export type BoardStore = ReturnType<typeof useBoardStore>;

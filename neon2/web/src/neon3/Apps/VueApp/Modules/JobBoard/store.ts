import {defineStore} from 'pinia';
import {PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";

export const useBoardStore = defineStore('jobBoard', {
  state(): State {
    return {
      applicationEmail: null,
      pricingPlan: null,
    };
  },
});

interface State {
  applicationEmail: string|null;
  pricingPlan: PricingPlan|null,
}


export type BoardStore = ReturnType<typeof useBoardStore>;

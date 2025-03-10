import {createApp} from "vue";
import App from "./dom/App.vue";

interface BackendInput {
  jobOffers: JobOffer[];
}

export function run(root: ShadowRoot): void {
  const backendInput = window['backendInput'] as BackendInput;
  const vueApp = createApp(App, {
    jobOffers: backendInput.jobOffers,
  });
  vueApp.mount(root.querySelector('#vueApplication')!);
}

export interface JobOffer {
  title: string;
  url: string;
  locations: string[];
  workMode: WorkMode;
  isFavourite: boolean;
  isNew: boolean;
  publishDate: string;
  tagNames: string[];
  companyName: string|null;
  companyLogoUrl: string|null;
  commentsCount: number;
  salaryFrom: number|null;
  salaryTo: number|null;
  salaryCurrency: Currency|null;
  salaryIncludesTax: boolean;
  salarySettlement: Settlement;
}

export type WorkMode = 'stationary'|'hybrid'|'fullyRemote';
export type Currency = 'PLN'|'EUR'|'USD'|'GBP'|'CHF';
export type Settlement = 'hourly'|'weekly'|'monthly'|'yearly';

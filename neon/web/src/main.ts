import "./style.scss";
import "./tailwind.css";
import {createApp} from 'vue';
import App from "./App.vue";

interface BackendInput {
  jobOffers: JobOffer[];
}

type Currency = 'PLN'|'EUR'|'USD'|'GBP'|'CHF';
type WorkMode = 'stationary'|'hybrid'|'fullyRemote';

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
  commentsCount: number;
  salaryFrom: number|null;
  salaryTo: number|null;
  salaryCurrency: Currency|null;
  salaryIncludesTax: boolean;
}

function shadowRootInitialized(root: ShadowRoot): void {
  const jobBoard = root.querySelector('#jobBoard')!;
  const backendInput = window['backendInput'] as BackendInput;
  const vueApp = createApp(App, {
    jobOffers: backendInput.jobOffers,
  });
  vueApp.mount(jobBoard);
}

window['NeonModule'] = {shadowRootInitialized};

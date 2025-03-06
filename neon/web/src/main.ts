import "./style.scss";
import "./tailwind.css";
import {createApp} from 'vue';
import App from "./App.vue";

interface BackendInput {
  jobOffers: JobOffer[];
}

export interface JobOffer {
  title: string;
  companyName: string;
  commentsCount: number;
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

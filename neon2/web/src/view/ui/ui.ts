import {createApp, h, ref, VNode} from 'vue';
import {JobOffer} from '../../jobBoard';
import {Toast} from '../view';
import JobBoard from './JobBoard.vue';

export interface ViewListener {
  createJob: (title: string, plan: 'free'|'paid') => void;
  editJob: (id: number, title: string) => void;
}

export interface UserInterface {
  mount(cssSelector: string): void;
  setJobOffers(jobOffers: JobOffer[]): void;
  setToast(toast: string|null): void;
  addViewListener(listener: ViewListener): void;
  addNavigationListener(listener: NavigationListener): void;
}

export type NavigationListener = () => void;

export class VueUi implements UserInterface {
  private jobOffers = ref<JobOffer[]>([]);
  private toast = ref<Toast|null>(null);
  private viewListener: ViewListener|null = null;
  private navigationListeners: NavigationListener[] = [];

  addViewListener(viewEventListener: ViewListener): void {
    this.viewListener = viewEventListener;
  }

  addNavigationListener(navigationListener: NavigationListener): void {
    this.navigationListeners.push(navigationListener);
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffers.value = jobOffers;
  }

  setToast(toast: Toast|null): void {
    this.toast.value = toast;
  }

  mount(cssSelector: string): void {
    const render = this.vueRender.bind(this);
    createApp({render}).mount(cssSelector);
  }

  private vueRender(): VNode {
    const that = this;
    return h(JobBoard, {
      jobOffers: that.jobOffers.value,
      toast: that.toast.value,
      onCreate(title: string, plan: 'free'|'paid'): void {
        that.viewListener!.createJob(title, plan);
      },
      onUpdate(id: number, title: string): void {
        that.viewListener!.editJob(id, title);
      },
      onNavigate(): void {
        that.navigationListeners.forEach(listener => listener());
      },
    });
  }
}

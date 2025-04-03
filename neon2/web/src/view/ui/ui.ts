import {createApp, h, Reactive, reactive, VNode} from 'vue';
import {JobOffer} from '../../jobBoard';
import {Toast} from '../view';
import JobBoard, {JobBoardProps, Screen} from './JobBoard.vue';

export {Screen} from './JobBoard.vue';

export interface ViewListener {
  createJob: (title: string, plan: 'free'|'paid') => void;
  updateJob: (id: number, title: string) => void;
  payForJob: (id: number) => void;
}

export interface UserInterface {
  mount(cssSelector: string): void;
  setJobOffers(jobOffers: JobOffer[]): void;
  setToast(toast: string|null): void;
  addViewListener(listener: ViewListener): void;
  addNavigationListener(listener: NavigationListener): void;
  addSearchListener(listener: SearchListener): void;
  setScreen(screen: Screen): void;
  setCurrentPaymentJobOfferId(jobOfferId: number): void;
}

export type NavigationListener = (screen: Screen) => void;
export type SearchListener = (searchPhrase: string) => void;

export class VueUi implements UserInterface {
  private vueState: Reactive<JobBoardProps> = reactive<JobBoardProps>({
    jobOffers: [],
    toast: null,
    screen: 'home',
    currentPaymentJobOfferId: null,
  });
  private viewListeners: ViewListener[] = [];
  private navigationListeners: NavigationListener[] = [];
  private searchListeners: SearchListener[] = [];

  addViewListener(viewListener: ViewListener): void {
    this.viewListeners.push(viewListener);
  }

  addNavigationListener(navigationListener: NavigationListener): void {
    this.navigationListeners.push(navigationListener);
  }

  addSearchListener(listener: SearchListener): void {
    this.searchListeners.push(listener);
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.vueState.jobOffers = jobOffers;
  }

  setScreen(screen: Screen): void {
    this.vueState.screen = screen;
  }

  setToast(toast: Toast|null): void {
    this.vueState.toast = toast;
  }

  setCurrentPaymentJobOfferId(jobOfferId: number): void {
    this.vueState.currentPaymentJobOfferId = jobOfferId;
  }

  mount(cssSelector: string): void {
    const render = this.vueRender.bind(this);
    createApp({render}).mount(cssSelector);
  }

  private vueRender(): VNode {
    const that = this;
    return h(JobBoard, {
      ...this.vueState,
      onCreate(title: string, plan: 'free'|'paid'): void {
        that.viewListeners.forEach(listener => listener.createJob(title, plan));
      },
      onUpdate(id: number, title: string): void {
        that.viewListeners.forEach(listener => listener.updateJob(id, title));
      },
      onPay(jobOfferId: number): void {
        that.viewListeners.forEach(listener => listener.payForJob(jobOfferId));
      },
      onNavigate(screen: Screen): void {
        that.navigationListeners.forEach(listener => listener(screen));
      },
      onSearch(searchPhrase: string): void {
        that.searchListeners.forEach(listener => listener(searchPhrase));
      },
    });
  }
}

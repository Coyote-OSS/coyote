import {createApp, h, Reactive, reactive, VNode} from 'vue';
import {JobOffer} from '../../jobBoard';
import {Toast} from '../view';
import JobBoard, {JobBoardProps} from './JobBoard.vue';

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
  addSearchListener(listener: SearchListener): void;
}

export type NavigationListener = () => void;
export type SearchListener = (searchPhrase: string) => void;

export class VueUi implements UserInterface {
  private vueState: Reactive<JobBoardProps> = reactive<JobBoardProps>({
    jobOffers: [],
    toast: null,
  });
  private viewListener: ViewListener|null = null;
  private navigationListeners: NavigationListener[] = [];
  private searchListeners: SearchListener[] = [];

  addViewListener(viewEventListener: ViewListener): void {
    this.viewListener = viewEventListener;
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

  setToast(toast: Toast|null): void {
    this.vueState.toast = toast;
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
        that.viewListener!.createJob(title, plan);
      },
      onUpdate(id: number, title: string): void {
        that.viewListener!.editJob(id, title);
      },
      onNavigate(): void {
        that.navigationListeners.forEach(listener => listener());
      },
      onSearch(searchPhrase: string): void {
        that.searchListeners.forEach(listener => listener(searchPhrase));
      },
    });
  }
}

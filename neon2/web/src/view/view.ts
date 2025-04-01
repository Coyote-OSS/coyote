import {createApp, h, ref} from 'vue';
import {JobOffer, Toast} from '../jobBoard';
import JobBoard from './JobBoard.vue';

export class View {
  private jobOffers = ref<JobOffer[]>([]);
  private _toast = ref<Toast|null>(null);

  constructor(private events: ViewEvents) {
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffers.value = jobOffers;
  }

  toast(toast: Toast|null): void {
    this._toast.value = toast;
  }

  mount(cssSelector: string): void {
    const that = this;
    const app = createApp({
      render() {
        return h(JobBoard, {
          jobOffers: that.jobOffers.value,
          toast: that._toast.value,
          onCreate(title: string, plan: 'free'|'paid'): void {
            that.events.createJob(title, plan);
          },
          onUpdate(id: number, title: string): void {
            that.events.editJob(id, title);
          },
          onNavigate(): void {
            that.toast(null);
          },
        });
      },
    });
    window.addEventListener('load', () => app.mount(cssSelector));
  }
}

export interface ViewEvents {
  createJob: (title: string, plan: 'free'|'paid') => void;
  editJob: (id: number, title: string) => void;
}

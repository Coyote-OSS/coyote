import {createApp, h, ref} from 'vue';
import {JobOffer} from '../jobBoard';
import JobBoard from './JobBoard.vue';

export class View {
  private jobOffers = ref<JobOffer[]>([]);

  constructor(private events: ViewEvents) {
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffers.value = jobOffers;
  }

  mount(cssSelector: string): void {
    const that = this;
    const app = createApp({
      render() {
        return h(JobBoard, {
          jobOffers: that.jobOffers.value,
          onCreate(title: string): void {
            that.events.onJobCreate(title);
          },
          onUpdate(id: number, title: string): void {
            that.events.onJobUpdate(id, title);
          },
        });
      },
    });
    window.addEventListener('load', () => app.mount(cssSelector));
  }
}

export interface ViewEvents {
  onJobCreate: (title: string) => void;
  onJobUpdate: (id: number, title: string) => void;
}

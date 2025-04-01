import {JobBoardBackend, LocalStorage} from "./backend";
import {JobBoard, JobOffer} from './jobBoard';
import {VueUi} from './view/ui/ui';
import {View} from "./view/view";

const view = new View(new VueUi());
const board = new JobBoard((jobOffers: JobOffer[]): void => view.setJobOffers(jobOffers));
const backend = new JobBoardBackend(new LocalStorage());

view.addEventListener({
  createJob(title: string, plan: 'free'|'paid'): void {
    backend.addJobOffer(title, plan, (id: number, expiresInDays: number): void => {
      board.jobOfferCreated({id, title, expiresInDays});
      view.toastCreated();
    });
  },
  editJob(id: number, title: string): void {
    backend.updateJobOffer(id, title, (): void => {
      board.jobOfferUpdated(id, title);
      view.toastEdited();
    });
  },
});

backend.initialJobOffers()
  .forEach(offer => board.jobOfferCreated({...offer}));

view.mount('#neonApplication');

import {JobBoardBackend, LocalStorage} from "./backend";
import {JobBoard, JobOffer, Toast} from './jobBoard';
import {View} from "./view/view";

const backend = new JobBoardBackend(new LocalStorage());
let board: JobBoard;
const view = new View({
  createJob(title: string, plan: 'free'|'paid'): void {
    backend.addJobOffer(title, plan, (id: number, expiresInDays: number) => {
      board.jobOfferCreated({
        id,
        title,
        expiresInDays,
      });
    });
  },
  editJob(id: number, title: string): void {
    backend.updateJobOffer(id, title, () => {
      board.jobOfferUpdated(id, title);
    });
  },
});

board = new JobBoard((jobOffers: JobOffer[]): void => view.setJobOffers(jobOffers));
board.onToast((toast: Toast|null): void => view.toast(toast));

const offers = backend.initialJobOffers();
offers.forEach(offer => board.jobOfferCreated({
  id: offer.id,
  title: offer.title,
  expiresInDays: offer.expiresInDays,
}));

view.mount('#neonApplication');

import {JobBoardBackend} from "./backend";
import {JobBoard, JobOffer} from './jobBoard';
import {VueUi} from './view/ui/ui';
import {View} from "./view/view";

const view = new View(new VueUi());
const board = new JobBoard((jobOffers: JobOffer[]): void => view.setJobOffers(jobOffers));
const backend = new JobBoardBackend();

view.addEventListener({
  createJob(title: string, plan: 'free'|'paid'): void {
    backend.addJobOffer(title, plan, (id: number, expiresInDays: number): void => {
      if (plan === 'free') {
        board.jobOfferCreated({id, title, expiresInDays, status: 'published'});
      }
      if (plan === 'paid') {
        board.jobOfferCreated({id, title, expiresInDays, status: 'awaitingPayment'});
      }
      view.jobOfferCreated(id, plan);
    });
  },
  updateJob(id: number, title: string): void {
    backend.updateJobOffer(id, title, (): void => {
      board.jobOfferUpdated(id, title);
      view.jobOfferEdited();
    });
  },
  payForJob(id: number): void {
    board.jobOfferPaid(id);
  },
});

backend.initialJobOffers()
  .forEach(offer => board.jobOfferCreated({...offer, status: 'published'}));

view.mount('#neonApplication');

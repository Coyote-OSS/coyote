import {BackendJobOffer, JobBoardBackend} from "./backend";
import {JobBoard, JobOffer} from './jobBoard';
import {VueUi} from './view/ui/ui';
import {View} from "./view/view";

const view = new View(new VueUi());
const board = new JobBoard((jobOffers: JobOffer[]): void => view.setJobOffers(jobOffers));
const backend = new JobBoardBackend();

view.addEventListener({
  createJob(title: string, plan: 'free'|'paid'): void {
    backend.addJobOffer(title, plan, (jobOffer: BackendJobOffer): void => {
      const {id, title, expiresInDays, status} = jobOffer;
      board.jobOfferCreated({id, title, expiresInDays, status});
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
    backend.initiateJobOfferPayment(id, (): void => {
      board.jobOfferPaid(id);
      view.jobOfferPaid();
    });
  },
});

backend.initialJobOffers()
  .forEach(offer => board.jobOfferCreated({...offer}));

view.mount('#neonApplication');

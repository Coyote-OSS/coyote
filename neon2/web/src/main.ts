import {JobBoard, JobOffer} from "./jobBoard";
import {View} from "./view/view";

let board: JobBoard;
const view = new View({
  onJobCreate(title: string, plan: 'free'|'paid'): void {
    board.addJobOffer(title, plan);
  },
  onJobUpdate(id: number, title: string): void {
    board.updateJobOffer(id, title);
  },
});
board = new JobBoard((jobOffers: JobOffer[]): void => {
  view.setJobOffers(jobOffers);
});
view.mount('#neonApplication');

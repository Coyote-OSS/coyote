import {JobBoard, JobOffer} from "./jobBoard";
import {View} from "./view/view";

let board: JobBoard;
const view = new View({
  onJobCreate: (title: string) => board.addJobOffer(title),
  onJobUpdate: (id: number, title: string) => board.updateJobOffer(id, title),
});
board = new JobBoard((jobOffers: JobOffer[]): void => {
  view.setJobOffers(jobOffers);
});
view.mount('#neonApplication');

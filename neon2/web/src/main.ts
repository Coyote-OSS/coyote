import {JobBoard} from "./jobBoard";

const board = new JobBoard((jobOfferTitle: string): void => {
  displayAddJobOffer(jobOfferTitle);
});

window.addEventListener('load', () => {
  document.getElementById('add')!
    .addEventListener('click', function () {
      const jobOfferTitle = document.querySelector('#jobOfferTitle') as HTMLInputElement;
      board.addJobOffer(jobOfferTitle.value);
    });
});

function displayAddJobOffer(jobOfferTitle: string): void {
  const jobOffer = document.createElement('li');
  jobOffer.textContent = jobOfferTitle;
  jobOffer.dataset.testid = 'jobOfferTitle';
  document.querySelector('#jobOffers')!.appendChild(jobOffer);
}

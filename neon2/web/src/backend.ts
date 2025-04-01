export class JobBoardBackend {
  addJobOffer(title: string, plan: 'free'|'paid', created: (id: number, expiresInDays: number) => void): void {
    const formData = new FormData();
    formData.append('jobOfferTitle', title);
    formData.append('jobOfferPlan', plan);
    fetch('/job-offers', {method: 'POST', body: formData})
      .then(response => response.json())
      .then((jobOffer: BackendJobOffer): void => {
        created(jobOffer.id, jobOffer.expiresInDays);
      });
  }

  updateJobOffer(id: number, title: string, updated: () => void): void {
    const formData = new FormData();
    formData.append('jobOfferId', id.toString());
    formData.append('jobOfferTitle', title);
    fetch('/job-offers', {method: 'PATCH', body: formData})
      .then(() => updated());
  }

  initialJobOffers(): BackendJobOffer[] {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.jobOffers;
  }
}

interface BackendInput {
  jobOffers: BackendJobOffer[];
}

interface BackendJobOffer {
  id: number;
  title: string;
  expiresInDays: number;
}

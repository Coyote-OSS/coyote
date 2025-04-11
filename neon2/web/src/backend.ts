export class JobBoardBackend {
  addJobOffer(title: string, plan: 'free'|'paid', created: (jobOffer: BackendJobOffer) => void): void {
    request('POST', '/neon2/job-offers', {jobOfferTitle: title, jobOfferPlan: plan})
      .then(response => response.json())
      .then((jobOffer: BackendJobOffer): void => created(jobOffer));
  }

  updateJobOffer(id: number, title: string, updated: () => void): void {
    request('PATCH', '/neon2/job-offers', {jobOfferId: id.toString(), jobOfferTitle: title})
      .then(() => updated());
  }

  initiateJobOfferPayment(id: number, initiated: () => void): void {
    request('POST', '/neon2/job-offers/payment', {jobOfferId: id.toString()})
      .then(() => initiated());
  }

  initialJobOffers(): BackendJobOffer[] {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.jobOffers;
  }
}

function request(method: string, url: string, body: object) {
  return fetch(url, {
    method,
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(body),
  });
}

interface BackendInput {
  jobOffers: BackendJobOffer[];
}

export interface BackendJobOffer {
  id: number;
  title: string;
  expiresInDays: number;
  status: BackendJobOfferStatus;
}

type BackendJobOfferStatus = 'published'|'awaitingPayment';

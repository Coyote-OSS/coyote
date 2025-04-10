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

  preparePayment(paymentId: string): Promise<BackendPreparedPayment> {
    return request('POST', '/neon2/job-offers/payment', {paymentId: paymentId})
      .then(response => response.json());
  }

  fetchPaymentStatus(paymentId: string): Promise<BackendPaymentStatus> {
    return fetch('/neon2/status?paymentId=' + paymentId)
      .then(response => response.json());
  }

  initialJobOffers(): BackendJobOffer[] {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.jobOffers;
  }

  testMode(): boolean {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.testMode;
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
  testMode: boolean;
}

export interface BackendJobOffer {
  id: number;
  title: string;
  expiresInDays: number;
  status: BackendJobOfferStatus;
  paymentId: string;
}

export type BackendJobOfferStatus = 'published'|'awaitingPayment';
export type BackendPaymentStatus = 'awaitingPayment'|'paymentComplete'|'paymentFailed';
export type BackendPreparedPayment = ProviderReady|ProviderNotReady;

interface ProviderReady {
  providerReady: true;
  paymentId: string;
  paymentToken: string;
}

interface ProviderNotReady {
  providerReady: false;
  paymentId: string;
  paymentToken: null;
}

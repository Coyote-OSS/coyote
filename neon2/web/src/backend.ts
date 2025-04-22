import {JobOffer} from "./jobBoard";
import {Currency, InvoiceInformation, LegalForm, PricingPlan, Rate, SubmitJobOffer, WorkExperience, WorkMode} from "./main";

function jobOfferFields(jobOffer: SubmitJobOffer): object {
  return {
    jobOfferTitle: jobOffer.title,
    jobOfferDescription: jobOffer.description,
    jobOfferCompanyName: jobOffer.companyName,
    jobOfferSalaryRangeFrom: jobOffer.salaryRangeFrom,
    jobOfferSalaryRangeTo: jobOffer.salaryRangeTo,
    jobOfferSalaryIsNet: jobOffer.salaryIsNet,
    jobOfferSalaryCurrency: jobOffer.salaryCurrency,
    jobOfferSalaryRate: jobOffer.salaryRate,
    jobOfferLocations: jobOffer.locations,
    jobOfferCompanyLogoUrl: jobOffer.companyLogoUrl,
    jobOfferTagNames: jobOffer.tagNames,
    jobOfferWorkMode: jobOffer.workMode,
    jobOfferLegalForm: jobOffer.legalForm,
    jobOfferExperience: jobOffer.experience,
  };
}

export class JobBoardBackend {
  addJobOffer(
    pricingPlan: PricingPlan,
    jobOffer: SubmitJobOffer,
    created: (jobOffer: BackendJobOffer) => void,
  ): void {
    request('POST', '/neon2/job-offers', {
      jobOfferPlan: pricingPlan,
      ...jobOfferFields(jobOffer),
    })
      .then(response => response.json())
      .then((jobOffer: BackendJobOffer): void => created(jobOffer));
  }

  updateJobOffer(id: number, jobOffer: SubmitJobOffer, updated: () => void): void {
    request('PATCH', '/neon2/job-offers', {
      jobOfferId: id.toString(),
      ...jobOfferFields(jobOffer),
    })
      .then(() => updated());
  }

  preparePayment(paymentId: string, invoiceInfo: InvoiceInformation): Promise<BackendPreparedPayment> {
    return request('POST', '/neon2/job-offers/payment', {
      paymentId,
      userId: this.userId(),
      ...invoiceInfoFields(invoiceInfo),
    })
      .then(response => response.json());
  }

  fetchPaymentStatus(paymentId: string): Promise<BackendPaymentStatus> {
    return fetch('/neon2/status?paymentId=' + paymentId)
      .then(response => response.json());
  }

  async publishJobOfferUsingBundle(jobOfferId: number): Promise<void> {
    await request('POST', '/neon2/job-offers/redeem-bundle', {jobOfferId, userId: this.userId()});
  }

  initialJobOffers(): BackendJobOffer[] {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.jobOffers;
  }

  initialPlanBundle(): BackendPlanBundle {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.planBundle;
  }

  private userId(): number {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.userId;
  }

  private csrfToken(): string {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.csrfToken;
  }

  testMode(): boolean {
    const backendInput = window['backendInput'] as BackendInput;
    return backendInput.testMode;
  }

  async uploadImageReturnUrl(file: File): Promise<string> {
    const formData = new FormData();
    formData.append('logo', file);
    return fetch('/Firma/Logo', {
      method: 'POST',
      body: formData,
      headers: {'X-CSRF-TOKEN': this.csrfToken()},
    })
      .then(response => response.json())
      .then(uploadedImage => uploadedImage.url);
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
  planBundle: BackendPlanBundle;
  userId: number;
  csrfToken: string;
}

export interface BackendJobOffer {
  id: number;
  expiresInDays: number;
  status: BackendJobOfferStatus;
  paymentId: string|null;
  fields: {
    title: string;
    description: string;
    companyName: string;
    salaryRangeFrom: number;
    salaryRangeTo: number;
    salaryIsNet: boolean;
    salaryCurrency: Currency;
    salaryRate: Rate;
    locations: string[];
    companyLogoUrl: string;
    tagNames: string[];
    workMode: WorkMode;
    legalForm: LegalForm;
    experience: WorkExperience;
  };
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

interface BackendPlanBundle {
  hasBundle: boolean;
  remainingJobOffers?: number;
  planBundleName?: 'strategic'|'growth'|'scale';
}

export function toJobOffer(jobOffer: BackendJobOffer): JobOffer {
  const {fields, ...operationalFields} = jobOffer;
  return {...operationalFields, ...fields, isNew: true, isFavourite: true};
}

function invoiceInfoFields(invoiceInfo: InvoiceInformation): object {
  return {
    invoiceVatId: invoiceInfo.vatId,
    invoiceCountryCode: invoiceInfo.countryCode,
    invoiceCompanyName: invoiceInfo.companyName,
    invoiceCompanyAddress: invoiceInfo.companyAddress,
    invoiceCompanyPostalCode: invoiceInfo.companyPostalCode,
    invoiceCompanyCity: invoiceInfo.companyCity,
  };
}

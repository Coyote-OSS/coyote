import {JobOffer} from "./jobBoard";
import {
  ApplicationMode,
  Country,
  Currency,
  HiringType,
  InvoiceInformation,
  LegalForm,
  PaidPricingPlan,
  PricingPlan,
  Rate,
  SubmitJobOffer,
  Tag,
  Theme,
  WorkExperience,
} from "./main";
import {parseWorkMode} from "./workMode";

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
    jobOfferTagNames: jobOffer.tags.map(tag => tag.tagName),
    jobOfferTagPriorities: jobOffer.tags.map(tag => tag.priority),
    jobOfferWorkModeRemoteRange: jobOffer.workModeRemoteRange,
    jobOfferLegalForm: jobOffer.legalForm,
    jobOfferExperience: jobOffer.experience,
    jobOfferCompanyWebsiteUrl: jobOffer.companyWebsiteUrl,
    jobOfferCompanyDescription: jobOffer.companyDescription,
    jobOfferCompanyPhotoUrls: jobOffer.companyPhotoUrls,
    jobOfferCompanyVideoUrl: jobOffer.companyVideoUrl,
    jobOfferCompanySizeLevel: jobOffer.companySizeLevel,
    jobOfferCompanyFundingYear: jobOffer.companyFundingYear,
    jobOfferCompanyAddress: jobOffer.companyAddress,
    jobOfferCompanyHiringType: jobOffer.companyHiringType,
    jobOfferApplicationMode: jobOffer.applicationMode,
    jobOfferApplicationEmail: jobOffer.applicationEmail,
    jobOfferApplicationExternalAts: jobOffer.applicationExternalAts,
  };
}

declare global {
  interface Window {
    backendInput: BackendInput;
    neonOnChangeTheme?(isDarkTheme: boolean): void;
  }
}

export class JobBoardBackend {
  private backendInput: BackendInput = window.backendInput;

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

  preparePayment(paymentId: string, invoiceInfo: InvoiceInformation): Promise<PreparePaymentResponse> {
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
    return this.backendInput.jobOffers;
  }

  initialPlanBundle(): BackendPlanBundle {
    return this.backendInput.planBundle;
  }

  private userId(): number {
    if (!this.backendInput.userId) {
      throw new Error('Failed to retrieve userId of an unauthenticated user.');
    }
    return this.backendInput.userId;
  }

  jobOfferApplicationEmail(): string {
    return this.backendInput.jobOfferApplicationEmail ?? '';
  }

  testMode(): boolean {
    return this.backendInput.testMode;
  }

  stripeKey(): string|null {
    return this.backendInput.stripePublishableKey;
  }

  paymentInvoiceCountries(): Country[] {
    return this.backendInput.paymentInvoiceCountries;
  }

  async uploadLogoReturnUrl(file: File): Promise<string> {
    const formData = new FormData();
    formData.append('logo', file);
    return fetch('/Firma/Logo', {
      method: 'POST',
      body: formData,
      headers: {'X-CSRF-TOKEN': this.backendInput.csrfToken},
    })
      .then(response => response.json())
      .then(uploadedImage => uploadedImage.url);
  }

  async uploadAssetReturnUrl(file: File): Promise<string> {
    const formData = new FormData();
    formData.append('asset', file);
    return fetch('/assets', {
      method: 'POST',
      body: formData,
      headers: {'X-CSRF-TOKEN': this.backendInput.csrfToken},
    })
      .then(response => response.json())
      .then(uploadedImage => uploadedImage.url);
  }

  isAuthenticated(): boolean {
    return this.backendInput.userId !== null;
  }

  onChangeTheme(listener: (theme: Theme) => void): void {
    listener(this.backendInput.darkTheme ? 'dark' : 'light');
    if (this.backendInput.themeMode === 'system') {
      listener(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    }
    window['neonOnChangeTheme'] = function (isDarkTheme: boolean): void {
      listener(isDarkTheme ? 'dark' : 'light');
    };
  }

  jobOfferPayment(jobOfferId: number): BackendPaymentIntent {
    return this.backendInput.jobOffers
      .find(jobOffer => jobOffer.id === jobOfferId)!
      .payment!;
  }

  jobOfferPayments(): JobOfferPaymentIntent[] {
    return this.backendInput
      .jobOffers
      .filter(jobOffer => jobOffer.payment !== null)
      .map(jobOffer => {
        return {
          jobOfferId: jobOffer.id,
          paymentIntent: jobOffer.payment!,
        };
      });
  }
}

export interface JobOfferPaymentIntent {
  jobOfferId: number;
  paymentIntent: BackendPaymentIntent;
}

function request(method: string, url: string, body: object) {
  return fetch(url, {
    method,
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(body),
  });
}

export interface BackendInput {
  jobOffers: BackendJobOffer[];
  testMode: boolean;
  planBundle: BackendPlanBundle;
  userId: number|null;
  jobOfferApplicationEmail: string|null;
  csrfToken: string;
  stripePublishableKey: string|null;
  paymentInvoiceCountries: Array<{countryCode: string; countryName: string}>;
  darkTheme: boolean;
  themeMode: 'dark'|'light'|'system';
}

export interface BackendJobOffer {
  id: number;
  expiresInDays: number;
  status: BackendJobOfferStatus;
  payment: BackendPaymentIntent|null;
  applicationUrl: string;
  slug: string;
  canEdit: boolean;
  isMine: boolean;
  isNew: boolean;
  fields: {
    title: string;
    description: string|null;
    salaryRangeFrom: number|null;
    salaryRangeTo: number|null;
    salaryIsNet: boolean;
    salaryCurrency: Currency;
    salaryRate: Rate;
    locations: BackendJobOfferLocation[];
    tagNames: string[];
    tagPriorities: BackendJobOfferTagPriority[];
    workModeRemoteRange: number;
    legalForm: LegalForm;
    experience: WorkExperience;
    applicationMode: ApplicationMode,
    applicationEmail: string|null,
    applicationExternalAts: string|null,
    companyName: string;
    companyLogoUrl: string|null;
    companyWebsiteUrl: string|null,
    companyDescription: string|null,
    companyPhotoUrls: string[],
    companyVideoUrl: string|null,
    companySizeLevel: number|null,
    companyFundingYear: number|null,
    companyAddress: BackendJobOfferLocation|null,
    companyHiringType: HiringType,
  };
}

export type BackendJobOfferTagPriority = 1|2|3;

export interface BackendJobOfferLocation {
  latitude: number;
  longitude: number;
  city: string|null;
  streetName: string|null;
  streetNumber: string|null;
  countryCode: string|null;
  postalCode: string|null;
}

export interface BackendPaymentIntent {
  paymentId: string;
  paymentPriceBase: number;
  paymentPriceVat: number;
  paymentPricingPlan: PaidPricingPlan;
}

export type BackendJobOfferStatus = 'published'|'awaitingPayment'|'expired';
export type BackendPaymentStatus = 'awaitingPayment'|'paymentComplete'|'paymentFailed';

export interface BackendPreparedPayment {
  paymentId: string;
  providerReady: boolean;
  paymentToken: string|null;
}

interface BackendPlanBundle {
  hasBundle: boolean;
  remainingJobOffers: number|null;
  planBundleName: 'strategic'|'growth'|'scale'|null;
}

export function toJobOffer(jobOffer: BackendJobOffer): JobOffer {
  const {fields, ...operationalFields} = jobOffer;
  return {
    ...operationalFields,
    ...fields,
    isFavourite: false,
    workMode: parseWorkMode(jobOffer.fields.workModeRemoteRange),
    tags: jobOfferTags(jobOffer),
  };
}

function jobOfferTags(jobOffer: BackendJobOffer): Tag[] {
  return jobOffer.fields.tagNames.map((tagName: string, index: number): Tag => {
    return {
      tagName,
      priority: jobOffer.fields.tagPriorities[index],
    };
  });
}

function invoiceInfoFields(invoiceInfo: InvoiceInformation): object {
  return {
    invoiceVatId: invoiceInfo.vatId || null,
    invoiceCountryCode: invoiceInfo.countryCode,
    invoiceCompanyName: invoiceInfo.companyName,
    invoiceCompanyAddress: invoiceInfo.companyAddress,
    invoiceCompanyPostalCode: invoiceInfo.companyPostalCode,
    invoiceCompanyCity: invoiceInfo.companyCity,
  };
}

export interface PreparePaymentResponse {
  status: 'success'|'failedInvalidVatId';
  preparedPayment?: BackendPreparedPayment;
}

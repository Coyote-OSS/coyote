import {
  ApplicationMode,
  Currency,
  HiringType,
  LegalForm,
  PaidPricingPlan,
  Rate,
  WorkExperience,
} from "../../Feature/JobBoard/Domain/Model";

declare global {
  interface Window {
    backendInput: BackendInput;
  }
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
  acceptanceTagNames: string[];
}

export interface BackendJobOffer {
  id: number;
  expiresInDays: number;
  expiryDate: string;
  status: BackendJobOfferStatus;
  payment: BackendPaymentIntent|null;
  applicationUrl: string;
  slug: string;
  canEdit: boolean;
  isMine: boolean;
  isNew: boolean;
  isFavourite: boolean;
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

export type BackendJobOfferTagPriority = 0|1|2|3;

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

export interface BackendPlanBundle {
  hasBundle: boolean;
  remainingJobOffers: number|null;
  planBundleName: 'strategic'|'growth'|'scale'|null;
}

export type BackendJobOfferStatus = 'published'|'awaitingPayment'|'expired';
export type BackendPaymentStatus = 'awaitingPayment'|'paymentComplete'|'paymentFailed';

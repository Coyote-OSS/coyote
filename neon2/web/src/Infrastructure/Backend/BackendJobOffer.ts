import {
  ApplicationMode,
  Currency,
  HiringType,
  LegalForm,
  Rate,
  WorkExperience,
} from "../../Domain/JobBoard/JobBoard";
import {JobOfferLocation} from "../../Domain/JobBoard/JobOfferLocation";
import {JobOfferTagPriority} from "../../Domain/JobBoard/JobOfferTagPriority";
import {PaymentIntent} from "../../Domain/JobBoard/PaymentIntent";

export interface BackendJobOffer {
  id: number;
  expiresInDays: number;
  expiryDate: string;
  status: 'published'|'awaitingPayment'|'expired';
  payment: PaymentIntent|null;
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
    locations: JobOfferLocation[];
    tagNames: string[];
    tagPriorities: JobOfferTagPriority[];
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
    companyAddress: JobOfferLocation|null,
    companyHiringType: HiringType,
  };
}

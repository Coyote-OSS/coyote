import {
  ApplicationMode,
  Currency,
  HiringType,
  JobOfferTag,
  LegalForm,
  Rate,
  WorkExperience,
  WorkMode,
} from "./JobBoard";
import {JobOfferLocation} from "./JobOfferLocation";

export interface JobOffer {
  id: number;
  expiresInDays: number;
  expiryDate: string;
  status: 'published'|'awaitingPayment'|'expired';
  isNew: boolean;
  isFavourite: boolean;
  applicationUrl: string;
  slug: string;
  canEdit: boolean;
  isMine: boolean;
  title: string;
  description: string|null;
  salaryRangeFrom: number|null;
  salaryRangeTo: number|null;
  salaryIsNet: boolean;
  salaryCurrency: Currency;
  salaryRate: Rate;
  locations: JobOfferLocation[];
  tags: JobOfferTag[];
  workMode: WorkMode;
  workModeRemoteRange: number;
  legalForm: LegalForm;
  experience: WorkExperience;
  applicationMode: ApplicationMode,
  applicationEmail: string|null,
  applicationExternalAts: string|null,
  companyName: string;
  companyLogoUrl: string|null;
  companyWebsiteUrl: string|null;
  companyDescription: string|null;
  companyPhotoUrls: string[];
  companyVideoUrl: string|null;
  companySizeLevel: number|null;
  companyFundingYear: number|null;
  companyAddress: JobOfferLocation|null;
  companyHiringType: HiringType;
}

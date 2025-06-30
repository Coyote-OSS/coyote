import {
  ApplicationMode,
  Currency,
  HiringType,
  JobOfferTag,
  LegalForm,
  Rate,
  WorkExperience,
} from "../../../Domain/JobBoard/JobBoard";
import {JobOfferLocation} from "../../../Domain/JobBoard/JobOfferLocation";

export interface SubmitJobOffer {
  title: string;
  description: string|null;
  salaryRangeFrom: number|null;
  salaryRangeTo: number|null;
  salaryIsNet: boolean;
  salaryCurrency: Currency;
  salaryRate: Rate;
  locations: JobOfferLocation[];
  tags: JobOfferTag[];
  workModeRemoteRange: number;
  legalForm: LegalForm;
  experience: WorkExperience;
  applicationMode: ApplicationMode;
  applicationEmail: string|null;
  applicationExternalAts: string|null;
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

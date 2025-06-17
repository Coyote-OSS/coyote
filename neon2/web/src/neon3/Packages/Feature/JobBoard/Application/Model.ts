import {Tag} from "../../../../../main";
import {BackendJobOfferLocation} from "../../../Core/Backend/backendInput";
import {ApplicationMode, Currency, HiringType, LegalForm, Rate, WorkExperience} from "../Domain/Model";
import {JobOffer} from "./JobOffer";

export interface SubmitJobOffer {
  title: string;
  description: string|null;
  salaryRangeFrom: number|null;
  salaryRangeTo: number|null;
  salaryIsNet: boolean;
  salaryCurrency: Currency;
  salaryRate: Rate;
  locations: BackendJobOfferLocation[];
  tags: Tag[];
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
  companyAddress: BackendJobOfferLocation|null;
  companyHiringType: HiringType;
}

export function toSubmitJobOffer(jobOffer: JobOffer): SubmitJobOffer {
  return jobOffer;
}

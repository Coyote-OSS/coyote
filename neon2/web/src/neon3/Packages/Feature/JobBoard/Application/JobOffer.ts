import {Tag} from "../../../../../main";
import {BackendJobOfferLocation} from "../../../Core/Backend/backendInput";
import {ApplicationMode, Currency, HiringType, LegalForm, Rate, WorkExperience, WorkMode} from "../Domain/Model";

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
  locations: BackendJobOfferLocation[];
  tags: Tag[];
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
  companyAddress: BackendJobOfferLocation|null;
  companyHiringType: HiringType;
}

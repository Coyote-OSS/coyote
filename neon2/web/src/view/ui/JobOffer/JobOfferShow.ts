
import {BackendJobOfferLocation} from "../../../neon3/Packages/Core/Backend/backendInput";
import {JobOffer} from "../../../neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {SubmitJobOffer} from "../../../neon3/Packages/Feature/JobBoard/Application/Model";
import {
  Currency,
  JobOfferTag,
  LegalForm,
  Rate,
  WorkExperience,
  WorkMode,
} from "../../../neon3/Packages/Feature/JobBoard/Domain/Model";
import {parseWorkMode} from "../../../neon3/Packages/Feature/JobBoard/Domain/workMode";

export interface JobOfferShow {
  title: string;
  description: string|null;
  expired: boolean;
  favourite: boolean;
  expiresInDays: number|null;
  expiryDate: string|null;
  locationCities: string[];
  tags: JobOfferTag[];
  workMode: WorkMode;
  legalForm: LegalForm;
  experience: WorkExperience;
  salaryRangeFrom: number|null;
  salaryRangeTo: number|null;
  salaryIsNet: boolean;
  salaryCurrency: Currency;
  salaryRate: Rate;
  companyName: string;
  companyLogoUrl: string|null;
  companyWebsiteUrl: string|null;
  companyDescription: string|null;
  companyFundingYear: number|null;
  companySizeLevel: number|null;
  companyVideoUrl: string|null;
  applyExternally: boolean;
  companyPhotoUrls: string[];
  companyAddress: CompanyAddress|null;
}

export interface CompanyAddress {
  latitude: number;
  longitude: number;
}

export function toJobOfferShow(jobOffer: JobOffer): JobOfferShow {
  let expired = jobOffer.status === 'expired';
  return {
    ...jobOffer,
    applyExternally: jobOffer.applicationMode === 'external-ats',
    locationCities: locationCities(jobOffer.locations),
    companyAddress: address(jobOffer),
    expired,
    expiresInDays: expired ? null : jobOffer.expiresInDays,
    expiryDate: expired ? jobOffer.expiryDate : null,
    favourite: jobOffer.isFavourite,
  };
}

export function fromSubmitToJobOfferShow(submit: SubmitJobOffer, expiresInDays: number): JobOfferShow {
  return {
    ...submit,
    applyExternally: submit.applicationMode === 'external-ats',
    locationCities: locationCities(submit.locations),
    workMode: parseWorkMode(submit.workModeRemoteRange),
    companyAddress: address(submit),
    expired: false,
    expiryDate: null,
    expiresInDays,
    favourite: false,
  };
}

function address(jobOffer: JobOffer|SubmitJobOffer): CompanyAddress|null {
  if (jobOffer.companyAddress === null) {
    return null;
  }
  return {
    latitude: jobOffer.companyAddress.latitude,
    longitude: jobOffer.companyAddress.longitude,
  };
}

function locationCities(locations: BackendJobOfferLocation[]): string[] {
  return locations
    .map(location => location.city)
    .filter(city => city !== null);
}

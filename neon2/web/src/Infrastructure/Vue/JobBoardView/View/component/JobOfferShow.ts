import {SubmitJobOffer} from "../../../../../Application/JobBoard/Port/SubmitJobOffer";
import {JobOfferLocation} from "../../../../../Domain/JobBoard/JobOfferLocation";
import {JobOffer} from "../../../../../Domain/JobBoard/JobOffer";
import {
  Currency,
  JobOfferTag,
  LegalForm,
  Rate,
  WorkExperience,
  WorkMode,
} from "../../../../../Domain/JobBoard/JobBoard";
import {parseWorkMode} from "../../../../../Domain/JobBoard/workMode";

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

function locationCities(locations: JobOfferLocation[]): string[] {
  return locations
    .map(location => location.city)
    .filter(city => city !== null);
}

import {Location} from "../../../location/LocationInput";
import {
  ApplicationMode,
  Currency,
  HiringType,
  LegalForm,
  Rate,
  SubmitJobOffer,
  Tag,
  WorkExperience,
} from "../../../main";
import {prependJsUrlProtocol, strippedNumericString} from "./ValidationBag";

export interface FormModel {
  title: string;
  description: string;
  salaryRangeFrom: string;
  salaryRangeTo: string;
  salaryCurrency: Currency;
  salaryRate: Rate;
  salaryIsNet: boolean;
  locations: Location[];
  tagNames: string;
  tags: Tag[];
  workModeRemoteRange: number;
  legalForm: LegalForm;
  experience: WorkExperience;
  applicationMode: ApplicationMode;
  applicationEmail: string;
  applicationExternalAts: string;
  companyName: string;
  companyLogoUrl: string|null;
  companyWebsiteUrl: string;
  companyDescription: string;
  companyPhotoUrls: string[];
  companyVideoUrl: string;
  companySizeLevel: number|null;
  companyFundingYear: string;
  companyAddress: Location|null;
  companyHiringType: HiringType;
}

export function toFormModel(jobOffer: SubmitJobOffer): FormModel {
  return {
    ...jobOffer,
    salaryRangeFrom: formatNumber(jobOffer.salaryRangeFrom),
    salaryRangeTo: formatNumber(jobOffer.salaryRangeTo),
    locations: jobOffer.locations,
    tagNames: jobOffer.tags.map(tag => tag.tagName).join(', '),
    description: jobOffer.description || '',
    applicationEmail: jobOffer.applicationEmail || '',
    applicationExternalAts: jobOffer.applicationExternalAts || '',
    companyWebsiteUrl: jobOffer.companyWebsiteUrl || '',
    companyDescription: jobOffer.companyDescription || '',
    companyVideoUrl: jobOffer.companyVideoUrl || '',
    companyAddress: jobOffer.companyAddress,
    companyFundingYear: jobOffer.companyFundingYear
      ? jobOffer.companyFundingYear.toString()
      : '',
  };
}

export function fromFormModel(formModel: FormModel): SubmitJobOffer {
  return {
    ...formModel,
    salaryRangeFrom: parseNumber(formModel.salaryRangeFrom),
    salaryRangeTo: parseNumber(formModel.salaryRangeTo),
    salaryIsNet: formModel.salaryIsNet,
    locations: formModel.locations,
    description: parseString(formModel.description),
    applicationEmail: parseString(formModel.applicationEmail),
    applicationExternalAts: parseString(formModel.applicationExternalAts),
    companyWebsiteUrl: parseString(prependJsUrlProtocol(formModel.companyWebsiteUrl)),
    companyDescription: parseString(formModel.companyDescription),
    companyVideoUrl: parseString(formModel.companyVideoUrl),
    companyAddress: formModel.companyAddress,
    companyFundingYear: parseNumber(formModel.companyFundingYear),
  };
}

function formatNumber(value: number|null): string {
  if (value === null) {
    return '';
  }
  return value.toString(10);
}

export function parseNumber(value: string): number|null {
  if (value.trim() === '') {
    return null;
  }
  const number = parseInt(strippedNumericString(value), 10);
  if (isNaN(number)) {
    throw new Error('Failed to parse number.');
  }
  return number;
}

function parseString(string: string): string|null {
  if (string.length) {
    return string.trim();
  }
  return null;
}

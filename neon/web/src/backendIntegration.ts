import {Currency, LegalForm, Rate, WorkMode} from "./filters";

export const initialJobOffers: BackendJobOffer[] = window['jobOffers'];

export interface BackendJobOffer {
  title: string;
  url: string;
  salary: BackendSalary|null;
  publishDate: string;
  workMode: WorkMode;
  locations: string[];
  companyName: string|null;
  companyLogoUrl: string|null;
  tagNames: string[];
  legalForm: LegalForm;
  isFavourite: boolean;
}

export interface BackendSalary {
  rangeFrom: number;
  rangeTo: number;
  currency: Currency;
  rate: Rate;
  isNet: boolean;
}

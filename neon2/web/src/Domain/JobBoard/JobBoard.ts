import {JobOfferTagPriority} from "./JobOfferTagPriority";

export type WorkMode = 'stationary'|'hybrid'|'fullyRemote';
export type LegalForm = 'employment'|'b2b'|'of-mandate'|'specific-task';
export type WorkExperience = 'intern'|'junior'|'mid-level'|'senior'|'lead'|'manager'|'not-provided';
export type Rate = 'monthly'|'hourly'|'yearly'|'weekly';
export type Currency = 'PLN'|'EUR'|'USD'|'GBP'|'CHF';
export type ApplicationMode = '4programmers'|'external-ats';
export type HiringType = 'direct'|'agency';

export interface InvoiceInformation {
  companyName: string,
  countryCode: string,
  vatId: string,
  companyAddress: string,
  companyPostalCode: string,
  companyCity: string,
}

export type PlanBundleName = 'strategic'|'growth'|'scale';
export type PricingPlan = 'free'|PaidPricingPlan;
export type PaidPricingPlan = 'premium'|PlanBundleName;

export interface JobOfferPayment {
  paymentPriceBase: number;
  paymentPriceVat: number;
  paymentPricingPlan: PaidPricingPlan;
}

export type PaymentUpdatedStatus = 'paymentComplete'|'paymentFailed';

export interface Tag {
  tagName: string;
  title: string|null;
  timesUsed: number;
}

export interface Country {
  countryCode: string;
  countryName: string;
}

export interface JobOfferTag {
  tagName: string;
  priority: JobOfferTagPriority;
}

export interface PaymentSummary {
  basePrice: number;
  vat: number;
  bundleSize: 1|3|5|20;
  vatIncluded: boolean;
}

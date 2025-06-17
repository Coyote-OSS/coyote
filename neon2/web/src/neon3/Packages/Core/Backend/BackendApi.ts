import {SubmitJobOffer} from "../../Feature/JobBoard/Application/Model";
import {InvoiceInformation, PricingPlan, Tag} from "../../Feature/JobBoard/Domain/Model";
import {Event} from "../../Feature/Vp/Model";
import {BackendJobOffer, BackendPaymentStatus} from "./backendInput";
import {request} from "./http";

export class BackendApi {
  addJobOffer(
    pricingPlan: PricingPlan,
    jobOffer: SubmitJobOffer,
    created: (jobOffer: BackendJobOffer) => void,
  ): void {
    request('POST', '/neon2/job-offers', {
      jobOfferPlan: pricingPlan,
      ...jobOfferFields(jobOffer),
    })
      .then(response => response.json())
      .then((jobOffer: BackendJobOffer): void => created(jobOffer));
  }

  updateJobOffer(id: number, jobOffer: SubmitJobOffer, updated: () => void): void {
    request('PATCH', '/neon2/job-offers', {
      jobOfferId: id.toString(),
      ...jobOfferFields(jobOffer),
    })
      .then(() => updated());
  }

  async markJobOfferAsFavourite(jobOfferId: number, favourite: boolean): Promise<void> {
    return request('POST', '/neon2/job-offers/favourite', {
      jobOfferId: jobOfferId.toString(),
      favourite,
    }).then(() => {});
  }

  preparePayment(userId: number, paymentId: string, invoiceInfo: InvoiceInformation): Promise<PreparePaymentResponse> {
    return request('POST', '/neon2/job-offers/payment', {
      paymentId,
      userId,
      ...invoiceInfoFields(invoiceInfo),
    })
      .then(response => response.json());
  }

  async publishJobOfferUsingBundle(jobOfferId: number, userId: number): Promise<void> {
    await request('POST', '/neon2/job-offers/redeem-bundle', {jobOfferId, userId});
  }

  fetchPaymentStatus(paymentId: string): Promise<BackendPaymentStatus> {
    return fetch('/neon2/status?paymentId=' + paymentId)
      .then(response => response.json());
  }

  async tagsAutocomplete(prompt: string): Promise<Tag[]> {
    return fetch('/completion/prompt/tags?q=' + encodeURIComponent(prompt))
      .then(response => response.json())
      .then(tags => tags.map((coyoteTag: any) => {
        return {
          tagName: coyoteTag.name,
          title: coyoteTag.real_name,
          timesUsed: coyoteTag.topics + coyoteTag.jobs + coyoteTag.microblogs,
        };
      }));
  }

  event(event: Event): Promise<void> {
    return request('POST', '/neon2/job-offers/event', event)
      .then(response => response.json());
  }
}

export interface PreparePaymentResponse {
  status: 'success'|'failedInvalidVatId';
  preparedPayment?: BackendPreparedPayment;
}

export interface BackendPreparedPayment {
  paymentId: string;
  providerReady: boolean;
  paymentToken: string|null;
}

function jobOfferFields(jobOffer: SubmitJobOffer): object {
  return {
    jobOfferTitle: jobOffer.title,
    jobOfferDescription: jobOffer.description,
    jobOfferCompanyName: jobOffer.companyName,
    jobOfferSalaryRangeFrom: jobOffer.salaryRangeFrom,
    jobOfferSalaryRangeTo: jobOffer.salaryRangeTo,
    jobOfferSalaryIsNet: jobOffer.salaryIsNet,
    jobOfferSalaryCurrency: jobOffer.salaryCurrency,
    jobOfferSalaryRate: jobOffer.salaryRate,
    jobOfferLocations: jobOffer.locations,
    jobOfferCompanyLogoUrl: jobOffer.companyLogoUrl,
    jobOfferTagNames: jobOffer.tags.map(tag => tag.tagName),
    jobOfferTagPriorities: jobOffer.tags.map(tag => tag.priority),
    jobOfferWorkModeRemoteRange: jobOffer.workModeRemoteRange,
    jobOfferLegalForm: jobOffer.legalForm,
    jobOfferExperience: jobOffer.experience,
    jobOfferCompanyWebsiteUrl: jobOffer.companyWebsiteUrl,
    jobOfferCompanyDescription: jobOffer.companyDescription,
    jobOfferCompanyPhotoUrls: jobOffer.companyPhotoUrls,
    jobOfferCompanyVideoUrl: jobOffer.companyVideoUrl,
    jobOfferCompanySizeLevel: jobOffer.companySizeLevel,
    jobOfferCompanyFundingYear: jobOffer.companyFundingYear,
    jobOfferCompanyAddress: jobOffer.companyAddress,
    jobOfferCompanyHiringType: jobOffer.companyHiringType,
    jobOfferApplicationMode: jobOffer.applicationMode,
    jobOfferApplicationEmail: jobOffer.applicationEmail,
    jobOfferApplicationExternalAts: jobOffer.applicationExternalAts,
  };
}

function invoiceInfoFields(invoiceInfo: InvoiceInformation): object {
  return {
    invoiceVatId: invoiceInfo.vatId || null,
    invoiceCountryCode: invoiceInfo.countryCode,
    invoiceCompanyName: invoiceInfo.companyName,
    invoiceCompanyAddress: invoiceInfo.companyAddress,
    invoiceCompanyPostalCode: invoiceInfo.companyPostalCode,
    invoiceCompanyCity: invoiceInfo.companyCity,
  };
}

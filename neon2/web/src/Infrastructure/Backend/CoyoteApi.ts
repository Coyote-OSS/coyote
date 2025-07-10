import {JobBoardApi, PreparePaymentResponse} from "../../Application/JobBoard/Port/JobBoardApi";
import {SubmitJobOffer} from "../../Application/JobBoard/Port/SubmitJobOffer";
import {NavigationApi} from "../../Application/Navigation/Port/NavigationApi";
import {InvoiceInformation, PricingPlan} from "../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../Domain/JobBoard/JobOffer";
import {PaymentIntent} from "../../Domain/JobBoard/PaymentIntent";
import {PaymentStatus} from "../../Domain/JobBoard/PaymentStatus";
import {Event} from "../../Domain/ValueProp/Model";
import {BackendJobOffer} from "./BackendJobOffer";
import {request} from "./http";
import {toJobOffer} from "./toJobOffer";

export class CoyoteApi implements JobBoardApi, NavigationApi {
  addJobOffer(
    pricingPlan: PricingPlan,
    jobOffer: SubmitJobOffer,
    created: (
      jobOffer: JobOffer,
      payment: PaymentIntent|null,
    ) => void,
  ): void {
    request('POST', '/neon2/job-offers', {
      jobOfferPlan: pricingPlan,
      ...jobOfferFields(jobOffer),
    })
      .then(response => response.json())
      .then((jobOffer: BackendJobOffer): void => {
        created(toJobOffer(jobOffer), jobOffer.payment);
      });
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

  fetchPaymentStatus(paymentId: string): Promise<PaymentStatus> {
    return fetch('/neon2/status?paymentId=' + paymentId)
      .then(response => response.json());
  }

  event(event: Event): Promise<void> {
    return request('POST', '/neon2/job-offers/event', event)
      .then(response => response.json());
  }

  toggleTheme(darkTheme: boolean): void {
    const colorScheme = darkTheme ? 'dark' : 'light';
    request('POST', '/User/Settings/Ajax', {colorScheme, lastColorScheme: colorScheme});
  }
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

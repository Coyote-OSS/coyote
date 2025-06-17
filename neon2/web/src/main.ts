import {JobBoardBackend, toJobOffer} from "./backend";
import {JobBoard} from './jobBoard';
import {locationDisplay} from "./neon3/Packages/Core/Acceptance/locationDisplay";
import {locationInput} from "./neon3/Packages/Core/Acceptance/locationInput";
import {paymentProvider} from "./neon3/Packages/Core/Acceptance/paymentProvider";
import {PaymentNotification, PaymentProvider} from "./neon3/Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./neon3/Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "./neon3/Packages/Core/Backend/BackendImageApi";
import {BackendJobOffer} from "./neon3/Packages/Core/Backend/backendInput";
import {isVatIncluded} from "./neon3/Packages/Core/Domain/vat";
import {Filter} from "./neon3/Packages/Feature/JobBoard/Application/filter";
import {JobOfferPayments} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferPayments";
import {InitiatePayment, SubmitJobOffer} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {PaymentService} from "./neon3/Packages/Feature/JobBoard/Application/PaymentService";
import {PlanBundle} from "./neon3/Packages/Feature/JobBoard/Application/PlanBundle";
import {bundleSize, remainingJobOffers} from "./neon3/Packages/Feature/JobBoard/Domain/bundleSize";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {PaymentStatus, PlanBundleName, PricingPlan} from "./neon3/Packages/Feature/JobBoard/Domain/Model";
import {JobOfferPaymentIntent} from "./neon3/Packages/Feature/JobBoard/JobBoard";
import {PaymentSummary} from "./neon3/Packages/Feature/JobBoard/Presenter/Model";
import {EventMetadata} from "./neon3/Packages/Feature/Vp/Model";
import {TagAutocompleteResult, VueUiFactory} from './ui';
import {View} from "./view";

const backendApi = new BackendApi();
const backend = new JobBoardBackend(backendApi);
const backendImageApi = new BackendImageApi(backend.csrfToken());
const ui = new VueUiFactory(
  locationInput(backend.testMode()),
  backend.isAuthenticated(),
  backendImageApi,
);
const view = new View(ui);
const board = new JobBoard((jobOffers: JobOffer[]): void => view.setJobOffers(jobOffers));
const _paymentProvider: PaymentProvider = paymentProvider(backend.testMode(), backend.stripeKey());
const payments = new PaymentService(backend, backendApi, _paymentProvider);
const jobOfferPayments = new JobOfferPayments();
const planBundle = new PlanBundle();
const _locationDisplay = locationDisplay(backend.testMode());

backend.jobOfferPayments()
  .forEach((paymentIntent: JobOfferPaymentIntent): void => jobOfferPayments.addJobOffer(paymentIntent));

ui.setViewListener({
  createJob(pricingPlan: PricingPlan, jobOffer: SubmitJobOffer): void {
    backendApi.addJobOffer(pricingPlan, jobOffer, (jobOffer: BackendJobOffer): void => {
      board.jobOfferCreated(toJobOffer(jobOffer));
      if (pricingPlan === 'free') {
        view.jobOfferCreatedFree(jobOffer.id);
      } else {
        jobOfferPayments.addJobOffer({jobOfferId: jobOffer.id, paymentIntent: jobOffer.payment!});
        ui.setPaymentSummary(paymentSummary(jobOffer.id));
        view.jobOfferCreatedRequirePayment(jobOffer.id);
      }
    });
  },
  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    ui.setJobOfferFavourite(jobOfferId, favourite);
    backendApi.markJobOfferAsFavourite(jobOfferId, favourite);
  },
  vatDetailsChanged(countryCode: string, vatId: string): void {
    ui.setVatIncluded(isVatIncluded(countryCode, vatId));
  },
  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    backendApi.updateJobOffer(jobOfferId, jobOffer, (): void => {
      board.jobOfferUpdated(jobOfferId, jobOffer);
      view.jobOfferEdited(jobOfferId);
    });
  },
  payForJob(initiatePayment: InitiatePayment): void {
    payments.initiatePayment(
      jobOfferPayments.paymentId(initiatePayment.jobOfferId),
      initiatePayment.invoiceInfo,
      initiatePayment.paymentMethod);
  },
  resumePayment(jobOfferId: number): void {
    ui.setPaymentSummary(paymentSummary(jobOfferId));
  },
  redeemBundle(jobOfferId: number): void {
    backendApi
      .publishJobOfferUsingBundle(jobOfferId, backend.userId())
      .then(() => {
        board.jobOfferPaid(jobOfferId);
        view.planBundleUsed();
        view.jobOfferPaid();
        planBundle.decrease();
      });
  },
  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void {
    if (action === 'mount') {
      _paymentProvider.mountCardInput(cssSelector!);
    } else {
      _paymentProvider.unmountCardInput();
    }
  },
  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void {
    _locationDisplay.mount(element, latitude, longitude);
  },
  assertUserAuthenticated(): boolean {
    if (backend.isAuthenticated()) {
      return true;
    }
    window.location.href = '/Login';
    return false;
  },
  apply(jobOffer: JobOffer): void {
    view.showValueProposition(jobOffer);
  },
  valuePropositionAccepted(
    jobOffer: JobOffer,
    event: ValuePropositionEvent,
    email?: string,
  ): void {
    const result = vpEvent(event, {jobOfferId: jobOffer.id, email});
    if (event === 'vpDeclined' || event === 'vpApply') {
      view.hideValueProposition();
      result.finally(() => jobOfferApply(jobOffer));
    }
  },
});

function vpEvent(eventName: string, metadata: EventMetadata): Promise<void> {
  return backendApi.event({eventName, metadata});
}

function jobOfferApply(jobOffer: JobOffer): void {
  if (jobOffer.applicationMode === 'external-ats') {
    window.open(jobOffer.applicationUrl, '_blank');
  } else {
    window.location.href = jobOffer.applicationUrl;
  }
}

export type ValuePropositionEvent = 'vpAccepted'|'vpDeclined'|'vpSubscribed'|'vpApply';

ui.setTagAutocomplete((tagPrompt: string, result: TagAutocompleteResult): void => {
  backend.tagsAutocomplete(tagPrompt).then(tags => result(tags));
});

function paymentSummary(jobOfferId: number): PaymentSummary {
  const payment = jobOfferPayments.jobOfferPayment(jobOfferId);
  return {
    bundleSize: bundleSize(payment.paymentPricingPlan),
    basePrice: payment.paymentPriceBase,
    vat: payment.paymentPriceVat,
    vatIncluded: true,
  };
}

payments.addEventListener({
  processingStarted(): void {
    ui.setPaymentProcessing(true);
    ui.setVatIdState('pending');
  },
  processingFinished(): void {
    ui.setPaymentProcessing(false);
  },
  paymentInitiationVatIdState(vatId: VatIdState): void {
    ui.setVatIdState(vatId);
  },
  notificationReceived(notification: PaymentNotification): void {
    ui.setPaymentNotification(notification);
  },
  statusChanged(paymentId: string, status: PaymentStatus): void {
    ui.setPaymentStatus(status);
    if (status === 'paymentComplete') {
      board.jobOfferPaid(jobOfferPayments.jobOfferId(paymentId));
      const pricingPlan = jobOfferPayments.pricingPlan(paymentId);
      if (pricingPlan !== 'premium') {
        planBundle.set(pricingPlan, remainingJobOffers(pricingPlan));
      }
      view.jobOfferPaid();
    }
  },
});

planBundle.addListener(function (plan: PlanBundleName, remainingJobOffers: number): void {
  view.setPlanBundle(plan, remainingJobOffers);
});

const bundle = backend.initialPlanBundle();
if (bundle.hasBundle) {
  planBundle.set(bundle.planBundleName!, bundle.remainingJobOffers!);
}

backend.initialJobOffers()
  .forEach(offer => board.jobOfferCreated(toJobOffer(offer)));

ui.setJobOfferApplicationEmail(backend.jobOfferApplicationEmail());
ui.setPaymentInvoiceCountries(backend.paymentInvoiceCountries());
ui.setJobOfferFilters(board.jobOfferFilters());
view.addFilterListener({
  filterChange(filter: Filter): void {
    ui.setJobOfferFilter(filter);
  },
});

ui.mount(document.querySelector('#neonApplication')!);

export interface UploadImage {
  (file: File): Promise<string>;
}

export interface UploadAssets {
  uploadLogo: UploadImage;
  uploadAsset: UploadImage;
}

export type VatIdState = 'valid'|'invalid'|'pending';

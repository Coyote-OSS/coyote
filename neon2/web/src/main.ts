import {JobBoardBackend, toJobOffer} from "./backend";
import {JobBoard} from './jobBoard';
import {JobBoardPresenter} from "./neon3/Apps/VueApp/Modules/JobBoard/JobBoardPresenter";
import {locationDisplay} from "./neon3/Packages/Core/Acceptance/locationDisplay";
import {locationInput} from "./neon3/Packages/Core/Acceptance/locationInput";
import {paymentProvider} from "./neon3/Packages/Core/Acceptance/paymentProvider";
import {PaymentNotification, PaymentProvider} from "./neon3/Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./neon3/Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "./neon3/Packages/Core/Backend/BackendImageApi";
import {BackendJobOffer} from "./neon3/Packages/Core/Backend/backendInput";
import {isVatIncluded} from "./neon3/Packages/Core/Domain/vat";
import {FilterRepository} from "./neon3/Packages/Feature/JobBoard/Application/FilterRepository";
import {JobOfferFilterService} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferFilterService";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {InitiatePayment, SubmitJobOffer} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {PaymentIntentRepository} from "./neon3/Packages/Feature/JobBoard/Application/PaymentIntentRepository";
import {PaymentService} from "./neon3/Packages/Feature/JobBoard/Application/PaymentService";
import {PlanBundleRepository} from "./neon3/Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {bundleSize, remainingJobOffers} from "./neon3/Packages/Feature/JobBoard/Domain/bundleSize";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {
  PaymentStatus,
  PaymentSummary,
  PlanBundleName,
  PricingPlan,
} from "./neon3/Packages/Feature/JobBoard/Domain/Model";
import {EventMetadata} from "./neon3/Packages/Feature/Vp/Model";
import {TagAutocomplete, TagAutocompleteResult, VueUiFactory} from './ui';
import {ViewListener} from "./ViewListener";

const filterRepo = new FilterRepository();
const jobOffersRepo = new JobOfferRepository();
const planBundleRepo = new PlanBundleRepository();

const backendApi = new BackendApi();
const backend = new JobBoardBackend(backendApi);
const backendImageApi = new BackendImageApi(backend.csrfToken());
const filterService = new JobOfferFilterService(jobOffersRepo);

const tagAutocomplete: TagAutocomplete = (tagPrompt: string, result: TagAutocompleteResult): void => {
  backend.tagsAutocomplete(tagPrompt).then(tags => result(tags));
};

const board = new JobBoard((jobOffers: JobOffer[]): void => {
  jobOffersRepo.setJobOffers(jobOffers);
  presenter.setJobOffers(filterService.filter(filterRepo));
});

const _paymentProvider: PaymentProvider = paymentProvider(backend.testMode(), backend.stripeKey());
const payments = new PaymentService(backend, backendApi, _paymentProvider);
const jobOfferPayments = new PaymentIntentRepository();
const _locationDisplay = locationDisplay(backend.testMode());

jobOfferPayments.initJobOffers(backend.jobOfferPayments());

const ui = new VueUiFactory(
  locationInput(backend.testMode()),
  backend.isAuthenticated(),
  backendImageApi,
  jobOffersRepo,
  planBundleRepo,
  filterRepo,
  filterService,
  tagAutocomplete);

const presenter = new JobBoardPresenter(ui.store, ui.screens);

const viewListener: ViewListener = {
  createJob(pricingPlan: PricingPlan, jobOffer: SubmitJobOffer): void {
    backendApi.addJobOffer(pricingPlan, jobOffer, (jobOffer: BackendJobOffer): void => {
      board.jobOfferCreated(toJobOffer(jobOffer));
      if (pricingPlan === 'free') {
        presenter.notifyJobOfferCreatedFree(jobOffer.id);
      } else {
        jobOfferPayments.addJobOffer({jobOfferId: jobOffer.id, paymentIntent: jobOffer.payment!});
        presenter.notifyJobOfferCreatedRequirePayment(
          jobOffer.id,
          paymentSummary(jobOffer.id));
      }
    });
  },
  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    ui.setJobOfferFavourite(jobOfferId, favourite);
    backendApi.markJobOfferAsFavourite(jobOfferId, favourite);
  },
  vatDetailsChanged(countryCode: string, vatId: string): void {
    presenter.notifyVatIncludedChanged(isVatIncluded(countryCode, vatId));
  },
  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    backendApi.updateJobOffer(jobOfferId, jobOffer, (): void => {
      board.jobOfferUpdated(jobOfferId, jobOffer);
      presenter.notifyJobOfferEdited(jobOfferId);
    });
  },
  payForJob(initiatePayment: InitiatePayment): void {
    payments.initiatePayment(
      jobOfferPayments.paymentId(initiatePayment.jobOfferId),
      initiatePayment.invoiceInfo,
      initiatePayment.paymentMethod);
  },
  resumePayment(jobOfferId: number): void {
    presenter.initRequirePayment(paymentSummary(jobOfferId));
  },
  redeemBundle(jobOfferId: number): void {
    backendApi
      .publishJobOfferUsingBundle(jobOfferId, backend.userId())
      .then(() => {
        board.jobOfferPaid(jobOfferId);
        presenter.notifyPlanBundleUsed();
        planBundleRepo.decrease();
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
  selectPlan(plan: PricingPlan): void {
    if (backend.isAuthenticated()) {
      presenter.notifyPlanSelected(plan);
    } else {
      window.location.href = '/Login';
    }
  },
  apply(jobOffer: JobOffer): void {
    presenter.showValueProposition(jobOffer);
  },
  valuePropositionAccepted(
    jobOffer: JobOffer,
    event: ValuePropositionEvent,
    email?: string,
  ): void {
    const result = vpEvent(event, {jobOfferId: jobOffer.id, email});
    if (event === 'vpDeclined' || event === 'vpApply') {
      presenter.hideValueProposition();
      result.finally(() => jobOfferApply(jobOffer));
    }
  },
};

ui.setViewListener(viewListener);

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
    presenter.notifyPaymentProcessingStarted();
  },
  processingFinished(): void {
    presenter.notifyPaymentProcessingFinished();
  },
  paymentInitiationVatIdState(vatId: VatIdState): void {
    presenter.notifyPaymentVatIdState(vatId);
  },
  notificationReceived(notification: PaymentNotification): void {
    presenter.notifyPaymentNotification(notification);
  },
  statusChanged(paymentId: string, status: PaymentStatus): void {
    presenter.setPaymentStatus(status);
    if (status === 'paymentComplete') {
      board.jobOfferPaid(jobOfferPayments.jobOfferId(paymentId));
      const pricingPlan = jobOfferPayments.pricingPlan(paymentId);
      if (pricingPlan !== 'premium') {
        planBundleRepo.set(pricingPlan, remainingJobOffers(pricingPlan));
      }
      presenter.notifyJobOfferPaid();
    }
  },
});

planBundleRepo.addListener(function (plan: PlanBundleName, remainingJobOffers: number): void {
  presenter.notifyPlanBundleChanged(plan, remainingJobOffers, remainingJobOffers > 0);
});

const bundle = backend.initialPlanBundle();
if (bundle.hasBundle) {
  planBundleRepo.set(bundle.planBundleName!, bundle.remainingJobOffers!);
}

backend.initialJobOffers()
  .forEach(offer => board.jobOfferCreated(toJobOffer(offer)));

presenter.initJobOfferApplicationEmail(backend.jobOfferApplicationEmail());
presenter.initPaymentInvoiceCountries(backend.paymentInvoiceCountries());
presenter.setJobOfferFilters(board.jobOfferFilters());

ui.mount(document.querySelector('#neonApplication')!);

export interface UploadImage {
  (file: File): Promise<string>;
}

export type VatIdState = 'valid'|'invalid'|'pending';

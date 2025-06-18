import {JobBoardBackend, toJobOffer} from "./backend";
import {JobBoard} from './jobBoard';
import {JobBoardPresenter} from "./neon3/Apps/VueApp/Modules/JobBoard/JobBoardPresenter";
import {locationDisplay} from "./neon3/Packages/Core/Acceptance/locationDisplay";
import {locationInput} from "./neon3/Packages/Core/Acceptance/locationInput";
import {paymentProvider} from "./neon3/Packages/Core/Acceptance/paymentProvider";
import {PaymentNotification, PaymentProvider} from "./neon3/Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./neon3/Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "./neon3/Packages/Core/Backend/BackendImageApi";
import {FilterRepository} from "./neon3/Packages/Feature/JobBoard/Application/FilterRepository";
import {JobBoardServiceFactory} from "./neon3/Packages/Feature/JobBoard/Application/JobBoardServiceFactory";
import {JobOfferFilterService} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferFilterService";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {VatIdState} from "./neon3/Packages/Feature/JobBoard/Application/Model";
import {PaymentIntentRepository} from "./neon3/Packages/Feature/JobBoard/Application/PaymentIntentRepository";
import {PaymentService} from "./neon3/Packages/Feature/JobBoard/Application/PaymentService";
import {PlanBundleRepository} from "./neon3/Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {TagAutocompleteResult} from "./neon3/Packages/Feature/JobBoard/Application/TagAutocomplete";
import {remainingJobOffers} from "./neon3/Packages/Feature/JobBoard/Domain/bundleSize";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {PaymentStatus, PlanBundleName} from "./neon3/Packages/Feature/JobBoard/Domain/Model";
import {VueUiFactory} from './ui';
import {ViewListener} from "./ViewListener";

const filterRepo = new FilterRepository();
const jobOffersRepo = new JobOfferRepository();
const planBundleRepo = new PlanBundleRepository();

const backendApi = new BackendApi();
const backend = new JobBoardBackend(backendApi);
const backendImageApi = new BackendImageApi(backend.csrfToken());
const filterService = new JobOfferFilterService(jobOffersRepo);

const board = new JobBoard((jobOffers: JobOffer[]): void => {
  jobOffersRepo.setJobOffers(jobOffers);
  presenter.setJobOffers(filterService.filter(filterRepo));
});

const _paymentProvider: PaymentProvider = paymentProvider(backend.testMode(), backend.stripeKey());
const payments = new PaymentService(backend, backendApi, _paymentProvider);
const jobOfferPayments = new PaymentIntentRepository();
const _locationDisplay = locationDisplay(backend.testMode());

jobOfferPayments.initJobOffers(backend.jobOfferPayments());

const factory = new JobBoardServiceFactory(
  locationInput(backend.testMode()),
  backendImageApi,
  jobOffersRepo,
  planBundleRepo,
  filterRepo,
  filterService,
  (tagPrompt: string, result: TagAutocompleteResult): void => {
    backend.tagsAutocomplete(tagPrompt).then(tags => result(tags));
  });

const ui = new VueUiFactory(backend.isAuthenticated(), jobOffersRepo, factory);
const presenter = new JobBoardPresenter(ui.store, ui.screens);

const viewListener: ViewListener = new ViewListener(
  backend,
  backendApi,
  presenter,
  _locationDisplay,
  board,
  _paymentProvider,
  payments,
  jobOfferPayments,
  planBundleRepo,
);

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

ui.mount(
  document.querySelector('#neonApplication')!,
  viewListener);

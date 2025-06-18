import {JobBoardBackend, toJobOffer} from "./backend";
import {JobBoard} from './jobBoard';
import {ViewModel} from "./neon3/Apps/VueApp/Modules/JobBoard/ViewModel";
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
  viewModel.setJobOffers(filterService.filter(filterRepo));
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
const viewModel = new ViewModel(ui.store, ui.screens);

const viewListener: ViewListener = new ViewListener(
  backend,
  backendApi,
  viewModel,
  _locationDisplay,
  board,
  _paymentProvider,
  payments,
  jobOfferPayments,
  planBundleRepo,
);

payments.addEventListener({
  processingStarted(): void {
    viewModel.notifyPaymentProcessingStarted();
  },
  processingFinished(): void {
    viewModel.notifyPaymentProcessingFinished();
  },
  paymentInitiationVatIdState(vatId: VatIdState): void {
    viewModel.notifyPaymentVatIdState(vatId);
  },
  notificationReceived(notification: PaymentNotification): void {
    viewModel.notifyPaymentNotification(notification);
  },
  statusChanged(paymentId: string, status: PaymentStatus): void {
    viewModel.setPaymentStatus(status);
    if (status === 'paymentComplete') {
      board.jobOfferPaid(jobOfferPayments.jobOfferId(paymentId));
      const pricingPlan = jobOfferPayments.pricingPlan(paymentId);
      if (pricingPlan !== 'premium') {
        planBundleRepo.set(pricingPlan, remainingJobOffers(pricingPlan));
      }
      viewModel.notifyJobOfferPaid();
    }
  },
});

planBundleRepo.addListener(function (plan: PlanBundleName, remainingJobOffers: number): void {
  viewModel.notifyPlanBundleChanged(plan, remainingJobOffers, remainingJobOffers > 0);
});
planBundleRepo.init(backend.initialPlanBundle());

backend.initialJobOffers()
  .forEach(offer => board.jobOfferCreated(toJobOffer(offer)));

viewModel.initJobOfferApplicationEmail(backend.jobOfferApplicationEmail());
viewModel.initPaymentInvoiceCountries(backend.paymentInvoiceCountries());
viewModel.setFiltersOptions(board.filterOptions());

ui.mount(
  document.querySelector('#neonApplication')!,
  viewListener);

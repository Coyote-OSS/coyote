import {createPinia} from "pinia";
import {createApp} from "vue";
import {JobBoardDeprecated} from './jobBoardDeprecated';
import {useBoardStore} from "./neon3/Apps/VueApp/Modules/JobBoard/store";
import JobBoard from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobBoard.vue";
import {jobBoardServiceInjectKey} from "./neon3/Apps/VueApp/Modules/JobBoard/View/vue";
import {ViewModel} from "./neon3/Apps/VueApp/Modules/JobBoard/ViewModel";
import {locationDisplay} from "./neon3/Packages/Core/Acceptance/locationDisplay";
import {locationInput} from "./neon3/Packages/Core/Acceptance/locationInput";
import {paymentProvider} from "./neon3/Packages/Core/Acceptance/paymentProvider";
import {PaymentNotification, PaymentProvider} from "./neon3/Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./neon3/Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "./neon3/Packages/Core/Backend/BackendImageApi";
import {JobBoardBackend} from "./neon3/Packages/Core/Backend/JobBoardBackend";
import {FilterRepository} from "./neon3/Packages/Feature/JobBoard/Application/FilterRepository";
import {JobBoardPresenter} from "./neon3/Packages/Feature/JobBoard/Application/JobBoardPresenter";
import {JobBoardServiceFactory} from "./neon3/Packages/Feature/JobBoard/Application/JobBoardServiceFactory";
import {JobOfferController} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferController";
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
import {Policy} from "./Policy";
import {Screens} from "./Screens";

const filterRepo = new FilterRepository();
const jobOffersRepo = new JobOfferRepository();
const planBundleRepo = new PlanBundleRepository();

const backendApi = new BackendApi();
const backend = new JobBoardBackend(backendApi);
const filterService = new JobOfferFilterService(jobOffersRepo);

const board = new JobBoardDeprecated((jobOffers: JobOffer[]): void => {
  jobOffersRepo.setJobOffers(jobOffers);
  viewModel.notifyJobOffersChanged(filterService.filter(filterRepo));
});

const _paymentProvider: PaymentProvider = paymentProvider(backend.testMode(), backend.stripeKey());
const payments = new PaymentService(backend, backendApi, _paymentProvider);
const paymentIntents = new PaymentIntentRepository();
paymentIntents.initJobOffers(backend.jobOfferPayments());

const factory = new JobBoardServiceFactory(
  locationInput(backend.testMode()),
  locationDisplay(backend.testMode()),
  new BackendImageApi(backend.csrfToken()),
  jobOffersRepo,
  planBundleRepo,
  filterRepo,
  filterService,
  (tagPrompt: string, result: TagAutocompleteResult): void => {
    backend.tagsAutocomplete(tagPrompt).then(tags => result(tags));
  });

const vueApp = createApp(JobBoard);
const pinia = createPinia();
vueApp.use(pinia);
const store = useBoardStore();
const screens = new Screens(new Policy(backend.isAuthenticated(), jobOffersRepo, store));
const viewModel = new ViewModel(store, screens);

const controller = new JobOfferController(
  backend,
  backendApi,
  viewModel,
  board,
  _paymentProvider,
  payments,
  paymentIntents,
  planBundleRepo);

const presenter = new JobBoardPresenter(jobOffersRepo);

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
      board.jobOfferPaid(paymentIntents.jobOfferId(paymentId));
      const pricingPlan = paymentIntents.pricingPlan(paymentId);
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
board.init(backend.initialJobOffers());

viewModel.initJobOfferApplicationEmail(backend.jobOfferApplicationEmail());
viewModel.initPaymentInvoiceCountries(backend.paymentInvoiceCountries());
viewModel.setFiltersOptions(presenter.filterOptions());

vueApp.provide(jobBoardServiceInjectKey, factory.create(screens, store, controller));
screens.useIn(vueApp);
vueApp.mount(document.querySelector('#neonApplication')!);

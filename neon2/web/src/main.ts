import {createPinia} from "pinia";
import {createApp} from "vue";
import {PaymentListenerAdapter} from "./neon3/Apps/VueApp/Modules/JobBoard/Infrastructure/PaymentListenerAdapter";
import {JobBoardService} from "./neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {useBoardStore} from "./neon3/Apps/VueApp/Modules/JobBoard/store";
import JobBoard from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobBoard.vue";
import {jobBoardServiceInjectKey} from "./neon3/Apps/VueApp/Modules/JobBoard/View/vue";
import {ViewModel} from "./neon3/Apps/VueApp/Modules/JobBoard/ViewModel";
import {NavigationService} from "./neon3/Apps/VueApp/Modules/Navigation/NavigationService";
import {navigationServiceInjectKey} from "./neon3/Apps/VueApp/Modules/Navigation/View/vue";
import {locationDisplay} from "./neon3/Packages/Core/Acceptance/locationDisplay";
import {locationInput} from "./neon3/Packages/Core/Acceptance/locationInput";
import {paymentProvider} from "./neon3/Packages/Core/Acceptance/paymentProvider";
import {PaymentProvider} from "./neon3/Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./neon3/Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "./neon3/Packages/Core/Backend/BackendImageApi";
import {JobBoardBackend} from "./neon3/Packages/Core/Backend/JobBoardBackend";
import {JobBoardPresenter} from "./neon3/Packages/Feature/JobBoard/Application/JobBoardPresenter";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {PaymentIntentRepository} from "./neon3/Packages/Feature/JobBoard/Application/PaymentIntentRepository";
import {PaymentService} from "./neon3/Packages/Feature/JobBoard/Application/PaymentService";
import {PlanBundleRepository} from "./neon3/Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {Policy} from "./neon3/Packages/Feature/JobBoard/Application/Policy";
import {PlanBundleListenerAdapter} from "./neon3/Packages/Feature/JobBoard/Infrastructure/PlanBundleListenerAdapter";
import {TagAutocompleteAdapter} from "./neon3/Packages/Feature/JobBoard/Infrastructure/TagAutocompleteAdapter";
import {Screens} from "./Screens";

const vueApp = createApp(JobBoard);
const pinia = createPinia();
vueApp.use(pinia);
const store = useBoardStore();

const jobOffersRepo = new JobOfferRepository();
const paymentIntents = new PaymentIntentRepository();
const planBundleRepo = new PlanBundleRepository();
const backendApi = new BackendApi();
const backend = new JobBoardBackend(backendApi);
const _paymentProvider: PaymentProvider = paymentProvider(backend.testMode(), backend.stripeKey());
const payments = new PaymentService(backend, backendApi, _paymentProvider);
const policy = new Policy(backend.isAuthenticated(), jobOffersRepo, store);
const screens = new Screens(policy);
const viewModel = new ViewModel(store);
const presenter = new JobBoardPresenter(jobOffersRepo);
const jobBoardService = new JobBoardService(
  viewModel,
  screens,
  locationInput(backend.testMode()),
  locationDisplay(backend.testMode()),
  new TagAutocompleteAdapter(backend),
  new BackendImageApi(backend.csrfToken()),
  backendApi,
  backend,
  jobOffersRepo,
  planBundleRepo,
  paymentIntents,
  payments,
  _paymentProvider);

payments.addEventListener(new PaymentListenerAdapter(viewModel, jobBoardService));
planBundleRepo.addListener(new PlanBundleListenerAdapter(viewModel));
paymentIntents.initJobOffers(backend.jobOfferPayments());
planBundleRepo.init(backend.initialPlanBundle());
jobBoardService.initJobOffers(backend.initialJobOffers());
viewModel.initJobOfferApplicationEmail(backend.jobOfferApplicationEmail());
viewModel.initPaymentInvoiceCountries(backend.paymentInvoiceCountries());
viewModel.setFiltersOptions(presenter.filterOptions());

vueApp.provide(jobBoardServiceInjectKey, jobBoardService);
vueApp.provide(navigationServiceInjectKey, new NavigationService(screens));
screens.useIn(vueApp);
vueApp.mount(document.querySelector('#neonApplication')!);

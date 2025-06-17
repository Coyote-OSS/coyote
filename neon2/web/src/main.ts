import {createPinia} from "pinia";
import {createApp} from "vue";
import {RouteComponentMap, RouteUrlMap, VueRouter} from "./Apps/VueApp/External/VueRouter";
import {PaymentListenerAdapter} from "./Apps/VueApp/Modules/JobBoard/Infrastructure/PaymentListenerAdapter";
import {JobBoardService} from "./Apps/VueApp/Modules/JobBoard/JobBoardService";
import {ScreenName} from "./Apps/VueApp/Modules/JobBoard/Model";
import {useBoardStore} from "./Apps/VueApp/Modules/JobBoard/store";
import JobBoard from "./Apps/VueApp/Modules/JobBoard/View/JobBoard.vue";
import JobOfferCreate from "./Apps/VueApp/Modules/JobBoard/View/JobOfferCreate.vue";
import JobOfferEdit from "./Apps/VueApp/Modules/JobBoard/View/JobOfferEdit.vue";
import JobOfferHome from "./Apps/VueApp/Modules/JobBoard/View/JobOfferHome.vue";
import JobOfferPaymentScreen from "./Apps/VueApp/Modules/JobBoard/View/JobOfferPaymentScreen.vue";
import JobOfferPricing from "./Apps/VueApp/Modules/JobBoard/View/JobOfferPricing.vue";
import JobOfferShowScreen from "./Apps/VueApp/Modules/JobBoard/View/JobOfferShowScreen.vue";
import {jobBoardServiceInjectKey} from "./Apps/VueApp/Modules/JobBoard/View/vue";
import {ViewModel} from "./Apps/VueApp/Modules/JobBoard/ViewModel";
import {locationDisplay} from "./Packages/Core/Acceptance/locationDisplay";
import {locationInput} from "./Packages/Core/Acceptance/locationInput";
import {paymentProvider} from "./Packages/Core/Acceptance/paymentProvider";
import {PaymentProvider} from "./Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "./Packages/Core/Backend/BackendImageApi";
import {JobBoardBackend} from "./Packages/Core/Backend/JobBoardBackend";
import {JobBoardNavigator} from "./Packages/Feature/JobBoard/Application/JobBoardNavigator";
import {JobBoardPresenter} from "./Packages/Feature/JobBoard/Application/JobBoardPresenter";
import {JobOfferRepository} from "./Packages/Feature/JobBoard/Application/JobOfferRepository";
import {PaymentIntentRepository} from "./Packages/Feature/JobBoard/Application/PaymentIntentRepository";
import {PaymentService} from "./Packages/Feature/JobBoard/Application/PaymentService";
import {PlanBundleRepository} from "./Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {Policy} from "./Packages/Feature/JobBoard/Application/Policy";
import {PlanBundleListenerAdapter} from "./Packages/Feature/JobBoard/Infrastructure/PlanBundleListenerAdapter";
import {TagAutocompleteAdapter} from "./Packages/Feature/JobBoard/Infrastructure/TagAutocompleteAdapter";

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

export const jobBoardUrls: RouteUrlMap<ScreenName> = {
  'home': '/Job',
  'show': '/Job/:slug/:id',
  'pricing': '/Job/pricing',
  'form': '/Job/new',
  'edit': '/Job/:id/edit',
  'payment': '/Job/:id/payment',
};
export const jobBoardComponents: RouteComponentMap<ScreenName> = {
  'home': JobOfferHome,
  'show': JobOfferShowScreen,
  'pricing': JobOfferPricing,
  'form': JobOfferCreate,
  'edit': JobOfferEdit,
  'payment': JobOfferPaymentScreen,
};

const vueRouter = new VueRouter<ScreenName>(jobBoardUrls, jobBoardComponents);
const viewModel = new ViewModel(store);
const presenter = new JobBoardPresenter(jobOffersRepo);
const jobBoardService = new JobBoardService(
  viewModel,
  new JobBoardNavigator(vueRouter, policy),
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
planBundleRepo.initPlanBundle(backend.initialPlanBundle());
paymentIntents.initJobOffers(backend.jobOfferPayments());
jobBoardService.initJobOffers(backend.initialJobOffers());
viewModel.initJobOfferApplicationEmail(backend.jobOfferApplicationEmail());
viewModel.initPaymentInvoiceCountries(backend.paymentInvoiceCountries());
viewModel.setFiltersOptions(presenter.filterOptions());

vueApp.provide(jobBoardServiceInjectKey, jobBoardService);
vueRouter.useIn(vueApp);
vueApp.mount(document.querySelector('#neonApplication')!);

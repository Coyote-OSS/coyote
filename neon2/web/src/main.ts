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
import {ViewModel as JobBoardViewModel} from "./Apps/VueApp/Modules/JobBoard/ViewModel";
import {NavigationService} from "./Apps/VueApp/Modules/Navigation/NavigationService";
import {useNavigationStore} from "./Apps/VueApp/Modules/Navigation/store";
import {navigationServiceInjectKey} from "./Apps/VueApp/Modules/Navigation/View/vue";
import {ViewModel as NavigationViewModel} from "./Apps/VueApp/Modules/Navigation/ViewModel";
import {locationDisplay} from "./Packages/Core/Acceptance/locationDisplay";
import {locationInput} from "./Packages/Core/Acceptance/locationInput";
import {paymentProvider} from "./Packages/Core/Acceptance/paymentProvider";
import {PaymentProvider} from "./Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "./Packages/Core/Backend/BackendImageApi";
import {JobBoardBackend} from "./Packages/Core/Backend/JobBoardBackend";
import {NavigationBackend} from "./Packages/Core/Backend/NavigationBackend";
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
const boardStore = useBoardStore();
const navigationStore = useNavigationStore();

const jobOffersRepo = new JobOfferRepository();
const paymentIntents = new PaymentIntentRepository();
const planBundleRepo = new PlanBundleRepository();
const backendApi = new BackendApi();
const jbBackend = new JobBoardBackend(backendApi);
const navigationBackend = new NavigationBackend();
const _paymentProvider: PaymentProvider = paymentProvider(jbBackend.testMode(), jbBackend.stripeKey());
const payments = new PaymentService(jbBackend, backendApi, _paymentProvider);
const policy = new Policy(jbBackend.isAuthenticated(), jobOffersRepo, boardStore);

const csrfToken = window.backendInput.csrfToken;
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
const jbViewModel = new JobBoardViewModel(boardStore);
const presenter = new JobBoardPresenter(jobOffersRepo);
const jobBoardService = new JobBoardService(
  jbViewModel,
  new JobBoardNavigator(vueRouter, policy),
  locationInput(jbBackend.testMode()),
  locationDisplay(jbBackend.testMode()),
  new TagAutocompleteAdapter(jbBackend),
  new BackendImageApi(jbBackend.csrfToken()),
  backendApi,
  jbBackend,
  jobOffersRepo,
  planBundleRepo,
  paymentIntents,
  payments,
  _paymentProvider);

const nvViewModel = new NavigationViewModel(navigationStore);

payments.addEventListener(new PaymentListenerAdapter(jbViewModel, jobBoardService));
planBundleRepo.addListener(new PlanBundleListenerAdapter(jbViewModel));
planBundleRepo.initPlanBundle(jbBackend.initialPlanBundle());
paymentIntents.initJobOffers(jbBackend.jobOfferPayments());
jobBoardService.initJobOffers(jbBackend.initialJobOffers());
jbViewModel.initJobOfferApplicationEmail(jbBackend.jobOfferApplicationEmail());
jbViewModel.initPaymentInvoiceCountries(jbBackend.paymentInvoiceCountries());
jbViewModel.setFiltersOptions(presenter.filterOptions());
nvViewModel.setAuthenticationState(jbBackend.isAuthenticated());
nvViewModel.setNavigationMenu(jbBackend.navigationMenu());
nvViewModel.setNavigationUser(jbBackend.navigationUser());

vueApp.provide(jobBoardServiceInjectKey, jobBoardService);
vueApp.provide(navigationServiceInjectKey, new NavigationService(vueRouter, navigationBackend, csrfToken));
vueRouter.useIn(vueApp);
vueApp.mount(document.querySelector('#neonApplication')!);

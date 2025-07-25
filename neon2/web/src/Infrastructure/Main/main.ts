import {createApp} from "vue";
import {JobBoardPresenter} from "../../Application/JobBoard/JobBoardPresenter";
import {JobOfferRepository} from "../../Application/JobBoard/JobOfferRepository";
import {PaymentIntentRepository} from "../../Application/JobBoard/PaymentIntentRepository";
import {PaymentService} from "../../Application/JobBoard/PaymentService";
import {PaymentProvider} from "../../Application/JobBoard/Port/PaymentProvider";
import {PlanBundleRepository} from "../../Domain/JobBoard/PlanBundleRepository";
import {applicationMode} from "../ApplicationMode/applicationMode";
import {BackendInput} from "../Backend/BackendInput";
import {BackendInputApplicationInbound} from "../Backend/BackendInputApplicationInbound";
import {CoyoteApi} from "../Backend/CoyoteApi";
import {CoyoteImageHosting} from "../Backend/CoyoteImageHosting";
import {CoyoteSocketClient} from "../Backend/CoyoteSocketClient";
import {RouteComponentMap, RouteUrlMap, VueRouter} from "../Vue/External/VueRouter";
import {JobBoardNavigator} from "../Vue/JobBoardView/JobBoardNavigator";
import {JobBoardService} from "../Vue/JobBoardView/JobBoardService";
import {JobBoardView} from "../Vue/JobBoardView/JobBoardView";
import {ScreenName} from "../Vue/JobBoardView/Model";
import {PaymentListenerAdapter} from "../Vue/JobBoardView/PaymentListenerAdapter";
import {PlanBundleListenerAdapter} from "../Vue/JobBoardView/PlanBundleListenerAdapter";
import JobBoard from "../Vue/JobBoardView/View/JobBoard.vue";
import JobOfferCreate from "../Vue/JobBoardView/View/JobOfferCreate.vue";
import JobOfferEdit from "../Vue/JobBoardView/View/JobOfferEdit.vue";
import JobOfferHome from "../Vue/JobBoardView/View/JobOfferHome.vue";
import JobOfferPaymentScreen from "../Vue/JobBoardView/View/JobOfferPaymentScreen.vue";
import JobOfferPricing from "../Vue/JobBoardView/View/JobOfferPricing.vue";
import JobOfferShowScreen from "../Vue/JobBoardView/View/JobOfferShowScreen.vue";
import {jobBoardServiceInjectKey} from "../Vue/JobBoardView/View/vue";
import {NavigationSocketListener} from "../Vue/NavigationSocketListener";
import {NavigationView} from "../Vue/NavigationView/NavigationView";
import {ThemeController} from "../Vue/NavigationView/ThemeController";
import {useNavigationStore} from "../Vue/NavigationView/View/navigationStore";
import {navigationServiceInjectKey} from "../Vue/NavigationView/View/vue";
import {VueNavigationService} from "../Vue/NavigationView/VueNavigationService";
import {pinia} from "../Vue/pinia";

import "../Vue/DesignSystem/tailwind.css";

declare global {
  interface Window {
    backendInput: BackendInput;
  }
}

const vueApp = createApp(JobBoard);
vueApp.use(pinia);

const jobOffersRepo = new JobOfferRepository();
const paymentIntents = new PaymentIntentRepository();
const planBundleRepo = new PlanBundleRepository();
const inbound = new BackendInputApplicationInbound(window.backendInput);
const coyoteApi = new CoyoteApi(inbound.csrfToken());
const mode = applicationMode(inbound);
const paymentProvider: PaymentProvider = mode.paymentProvider();
const payments = new PaymentService(inbound, coyoteApi, paymentProvider);
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
const jbView = new JobBoardView();
const presenter = new JobBoardPresenter(jobOffersRepo);
const jobBoardService = new JobBoardService(
  jbView,
  new JobBoardNavigator(
    vueRouter,
    inbound.isAuthenticated(),
    jobOffersRepo,
    jbView),
  mode.locationInput(),
  mode.locationDisplay(),
  mode.tagAutocomplete(),
  new CoyoteImageHosting(inbound.csrfToken()),
  coyoteApi,
  coyoteApi,
  inbound,
  jobOffersRepo,
  planBundleRepo,
  paymentIntents,
  payments,
  paymentProvider);
const nvView = new NavigationView(useNavigationStore());

payments.addEventListener(new PaymentListenerAdapter(jbView, jobBoardService));
planBundleRepo.addListener(new PlanBundleListenerAdapter(jbView));
planBundleRepo.initPlanBundle(inbound.initialPlanBundle());
paymentIntents.initJobOffers(inbound.jobOfferPayments());
jobBoardService.initJobOffers(inbound.initialJobOffers());
jbView.initJobOfferApplicationEmail(inbound.jobOfferApplicationEmail());
jbView.initPaymentInvoiceCountries(inbound.paymentInvoiceCountries());
jbView.setFiltersOptions(presenter.filterOptions());
nvView.setNavigationForumMenu(window.backendInput.navigationForumMenu);
nvView.setUser(window.backendInput.navigationUser);
nvView.setDarkTheme(window.backendInput.darkTheme);

vueApp.provide(jobBoardServiceInjectKey, jobBoardService);
vueApp.provide(navigationServiceInjectKey, new VueNavigationService(
  vueRouter,
  inbound.csrfToken(),
  nvView,
  new ThemeController(nvView, coyoteApi),
  coyoteApi,
  mode.searchPrompt(),
));
vueRouter.useIn(vueApp);
vueApp.mount(document.querySelector('#neonApplication')!);

const websocketUrl = inbound.websocketUrl();
if (websocketUrl) {
  const client = new CoyoteSocketClient(websocketUrl, inbound.websocketSubscribeCommand()!, new NavigationSocketListener());
  client.start();
}

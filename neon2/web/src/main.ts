import {createPinia} from "pinia";
import {createApp} from "vue";
import {PaymentListenerAdapter} from "./neon3/Apps/VueApp/Modules/JobBoard/Infrastructure/PaymentListenerAdapter";
import {JobBoardService} from "./neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {useBoardStore} from "./neon3/Apps/VueApp/Modules/JobBoard/store";
import JobBoard from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobBoard.vue";
import {jobBoardServiceInjectKey} from "./neon3/Apps/VueApp/Modules/JobBoard/View/vue";
import {ViewModel} from "./neon3/Apps/VueApp/Modules/JobBoard/ViewModel";
import {locationDisplay} from "./neon3/Packages/Core/Acceptance/locationDisplay";
import {locationInput} from "./neon3/Packages/Core/Acceptance/locationInput";
import {paymentProvider} from "./neon3/Packages/Core/Acceptance/paymentProvider";
import {PaymentProvider} from "./neon3/Packages/Core/Application/PaymentProvider";
import {BackendApi} from "./neon3/Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "./neon3/Packages/Core/Backend/BackendImageApi";
import {JobBoardBackend} from "./neon3/Packages/Core/Backend/JobBoardBackend";
import {FilterRepository} from "./neon3/Packages/Feature/JobBoard/Application/FilterRepository";
import {JobBoardPresenter} from "./neon3/Packages/Feature/JobBoard/Application/JobBoardPresenter";
import {JobOfferFilterService} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferFilterService";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {PaymentIntentRepository} from "./neon3/Packages/Feature/JobBoard/Application/PaymentIntentRepository";
import {PaymentService} from "./neon3/Packages/Feature/JobBoard/Application/PaymentService";
import {PlanBundleRepository} from "./neon3/Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {TagAutocompleteResult} from "./neon3/Packages/Feature/JobBoard/Application/TagAutocomplete";
import {PlanBundleName} from "./neon3/Packages/Feature/JobBoard/Domain/Model";
import {Policy} from "./Policy";
import {Screens} from "./Screens";

const filterRepo = new FilterRepository();
const jobOffersRepo = new JobOfferRepository();
const planBundleRepo = new PlanBundleRepository();
const backendApi = new BackendApi();
const backend = new JobBoardBackend(backendApi);
const filterService = new JobOfferFilterService(jobOffersRepo);
const _paymentProvider: PaymentProvider = paymentProvider(backend.testMode(), backend.stripeKey());
const payments = new PaymentService(backend, backendApi, _paymentProvider);
const paymentIntents = new PaymentIntentRepository();

const tagAutocomplete = (tagPrompt: string, result: TagAutocompleteResult): void => {
  backend.tagsAutocomplete(tagPrompt).then(tags => result(tags));
};

const vueApp = createApp(JobBoard);
const pinia = createPinia();
vueApp.use(pinia);
const store = useBoardStore();
const screens = new Screens(new Policy(backend.isAuthenticated(), jobOffersRepo, store));
const viewModel = new ViewModel(store, screens);
const presenter = new JobBoardPresenter(jobOffersRepo);

const jobBoardService = new JobBoardService(
  viewModel,
  store,
  screens,
  locationInput(backend.testMode()),
  locationDisplay(backend.testMode()),
  tagAutocomplete,
  new BackendImageApi(backend.csrfToken()),
  backendApi,
  backend,
  jobOffersRepo,
  planBundleRepo,
  filterRepo,
  filterService,
  paymentIntents,
  payments,
  _paymentProvider);

payments.addEventListener(new PaymentListenerAdapter(viewModel, jobBoardService));

planBundleRepo.addListener(function (plan: PlanBundleName, remainingJobOffers: number): void {
  viewModel.notifyPlanBundleChanged(plan, remainingJobOffers, remainingJobOffers > 0);
});

paymentIntents.initJobOffers(backend.jobOfferPayments());
planBundleRepo.init(backend.initialPlanBundle());
jobBoardService.initJobOffers(backend.initialJobOffers());
viewModel.initJobOfferApplicationEmail(backend.jobOfferApplicationEmail());
viewModel.initPaymentInvoiceCountries(backend.paymentInvoiceCountries());
viewModel.setFiltersOptions(presenter.filterOptions());

vueApp.provide(jobBoardServiceInjectKey, jobBoardService);
screens.useIn(vueApp);
vueApp.mount(document.querySelector('#neonApplication')!);

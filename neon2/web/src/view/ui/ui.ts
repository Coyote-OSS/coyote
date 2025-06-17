import {createPinia} from "pinia";
import {createApp, h, reactive} from 'vue';
import {JobOfferFilter} from "../../jobOfferFilter";
import {JobOfferFilters, UploadAssets, ValuePropositionEvent, VatIdState} from "../../main";
import {JobBoardService} from "../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {useBoardStore} from "../../neon3/Apps/VueApp/Modules/JobBoard/store";
import {jobBoardServiceInjectKey} from "../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import {LocationInput} from "../../neon3/Packages/Core/Application/LocationInput";
import {PaymentNotification} from "../../neon3/Packages/Core/Application/PaymentProvider";
import {InitiatePayment, SubmitJobOffer} from "../../neon3/Packages/Feature/JobBoard/Application/Model";
import {JobOffer} from "../../neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {
  Country,
  PaymentStatus,
  PlanBundleName,
  PricingPlan,
  Tag,
} from "../../neon3/Packages/Feature/JobBoard/Domain/Model";
import {PaymentSummary} from "../../neon3/Packages/Feature/JobBoard/Presenter/Model";
import {Toast, View} from '../view';
import JobBoard from './JobBoard.vue';
import {JobBoardProperties} from "./JobBoardProperties";
import {emptyJobOfferFilter} from "./JobOffer/JobOfferFilters";
import {Policy} from "./screen/Policy";
import {RouteProperties, Screens} from "./screen/Screens";

export type Screen = 'home'|'edit'|'form'|'payment'|'pricing'|'show';

/**
 * @deprecated
 */
export interface ViewListener {
  createJob(plan: PricingPlan, jobOffer: SubmitJobOffer): void;
  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void;
  payForJob(payment: InitiatePayment): void;
  resumePayment(jobOfferId: number): void;
  redeemBundle(jobOfferId: number): void;
  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void;
  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void;
  vatDetailsChanged(countryCode: string, vatId: string): void;
  assertUserAuthenticated(): boolean;
  markAsFavourite(jobOfferId: number, favourite: boolean): void;
  apply(jobOffer: JobOffer): void;
  valuePropositionAccepted(
    jobOffer: JobOffer,
    event: ValuePropositionEvent,
    email?: string): void;
}

export interface NavigationListener {
  setScreen(screen: Screen, jobOfferId: number|null): void;
  showJobOfferForm(): void;
}

export interface FilterListener {
  filter(filter: JobOfferFilter): void;
  filterOnlyMine(onlyMine: boolean): void;
}

/**
 * @deprecated
 */
export interface UiController {
  showForm(): void;
  selectPlan(plan: PricingPlan): void;
  navigate(screen: Screen, jobOfferId: number|null): void;
  filter(filter: JobOfferFilter): void;
  applyForJob(jobOfferId: number): void;
  showJobOffer(jobOffer: JobOffer): void;
  jobOfferUrl(jobOffer: JobOffer): string;
  filterOnlyMine(onlyMine: boolean): void;
  resumePayment(jobOfferId: number): void;
  markAsFavourite(jobOfferId: number, favourite: boolean): void;
  findJobOffer(jobOfferId: number): JobOffer|null;
  valuePropositionAccepted(event: ValuePropositionEvent, email?: string): void;
}

export type CanEdit = (jobOfferId: number) => boolean;
export type PricingPlanSelected = () => boolean;

export type TagAutocomplete = (tagPrompt: string, result: TagAutocompleteResult) => void;
export type TagAutocompleteResult = (tags: Tag[]) => void;

export class VueUi {
  private readonly gate: Policy;
  private readonly screens: Screens;
  private readonly vueState: JobBoardProperties;
  private readonly filterListeners: FilterListener[] = [];
  private navigationListener: NavigationListener|null = null;
  private view: View|null = null;
  private viewListener: ViewListener|null = null;
  private uiController: UiController;
  private tagAutocomplete: TagAutocomplete|null = null;
  private _upload: UploadAssets|null = null;

  constructor(locationInput: LocationInput, isAuthenticated: boolean) {
    this.uiController = {
      showForm: this.showForm.bind(this),
      selectPlan: this.selectPlan.bind(this),
      navigate: this.navigate.bind(this),
      filter: this.filter.bind(this),
      filterOnlyMine: this.filterOnlyMine.bind(this),
      applyForJob: this.applyForJob.bind(this),
      showJobOffer: this.showJobOffer.bind(this),
      jobOfferUrl: this.jobOfferUrl.bind(this),
      resumePayment: this.resumePayment.bind(this),
      markAsFavourite: this.markAsFavourite.bind(this),
      findJobOffer: this.findJobOfferReactive.bind(this),
      valuePropositionAccepted: this.valuePropositionAccepted.bind(this),
    };

    this.vueState = reactive<JobBoardProperties>({
      jobOffers: [],
      jobOfferFilters: {
        tags: [],
        locations: [],
      },
      jobOfferFilter: emptyJobOfferFilter(),
      toast: null,
      screen: 'home',
      paymentNotification: null,
      paymentStatus: null,
      planBundle: null,
      pricingPlan: null,
      applicationEmail: null,
      paymentSummary: null,
      paymentVatIdState: 'valid',
      invoiceCountries: null,
      locationInput,
      vpVisibleFor: null,
      paymentProcessing: false,
    });
    this.gate = new Policy(
      isAuthenticated,
      (jobOfferId: number): boolean => this.findJobOffer(jobOfferId)?.canEdit ?? false,
      () => this.vueState.pricingPlan !== null,
    );
    this.screens = new Screens({
      routeProperties: (jobOfferId: number|null): RouteProperties => {
        return {
          routeJobOfferId: jobOfferId,
          routeJobOffer: jobOfferId ? this.findJobOffer(jobOfferId) : null,
        };
      },
      resumePayment: (jobOfferId: number): void => {
        this.viewListener!.resumePayment(jobOfferId);
      },
    }, this.gate);
  }

  setView(view: View): void {
    this.view = view;
  }

  private showForm(): void {
    this.navigationListener!.showJobOfferForm();
  }

  private selectPlan(plan: PricingPlan): void {
    if (this.viewListener!.assertUserAuthenticated()) {
      this.vueState.pricingPlan = plan;
      this.setScreen('form', null);
    }
  }

  private navigate(screen: Screen, jobOfferId: number|null): void {
    this.navigationListener!.setScreen(screen, jobOfferId);
  }

  setScreen(screen: Screen, jobOfferId: number|null): void {
    this.screens.navigate(screen, jobOfferId);
    window.scrollTo(0, 0);
  }

  private filter(filter: JobOfferFilter): void {
    this.filterListeners.forEach(listener => listener.filter(filter));
  }

  private filterOnlyMine(onlyMine: boolean): void {
    this.filterListeners.forEach(listener => listener.filterOnlyMine(onlyMine));
  }

  private applyForJob(jobOfferId: number): void {
    this.viewListener!.apply(this.findJobOffer(jobOfferId)!);
  }

  private markAsFavourite(jobOfferId: number, favourite: boolean): void {
    this.viewListener!.markAsFavourite(jobOfferId, favourite);
  }

  private showJobOffer(jobOffer: JobOffer): void {
    this.screens.showJobOffer(jobOffer);
  }

  private jobOfferUrl(jobOffer: JobOffer): string {
    return this.screens.jobOfferUrl(jobOffer);
  }

  private resumePayment(jobOfferId: number): void {
    this.screens.navigate('payment', jobOfferId);
  }

  setViewListener(viewListener: ViewListener): void {
    this.viewListener = viewListener;
  }

  setNavigationListener(navigationListener: NavigationListener): void {
    this.navigationListener = navigationListener;
  }

  addFilterListener(listener: FilterListener): void {
    this.filterListeners.push(listener);
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.vueState.jobOffers = jobOffers;
  }

  setJobOfferFilters(filters: JobOfferFilters): void {
    this.vueState.jobOfferFilters = filters;
  }

  setJobOfferFilter(filter: JobOfferFilter): void {
    this.vueState.jobOfferFilter = filter;
  }

  setToast(toast: Toast|null): void {
    this.vueState.toast = toast;
  }

  private findJobOffer(jobOfferId: number): JobOffer|null {
    return this.view!.findJobOffer(jobOfferId);
  }

  private findJobOfferReactive(jobOfferId: number): JobOffer {
    const jobOffer = this.vueState.jobOffers.find(o => o.id === jobOfferId);
    if (jobOffer) {
      return jobOffer;
    }
    const nonReactive = this.findJobOffer(jobOfferId);
    if (nonReactive) {
      return nonReactive;
      // TODO, currently, only offers in list are reactive; 
      //       but offers outside of list (like mine, expired) need to be reactive too
    }
    throw new Error(
      'Failed to render job offer.' +
      ' offers in domain' + this.view!.jobOffers.length +
      ' offers in view' + this.vueState.jobOffers.length);
  }

  setPaymentNotification(notification: PaymentNotification): void {
    this.vueState.paymentNotification = notification;
  }

  setPaymentStatus(status: PaymentStatus): void {
    this.vueState.paymentStatus = status;
  }

  setPlanBundle(bundleName: PlanBundleName, remainingJobOffers: number, canRedeem: boolean): void {
    this.vueState.planBundle = {bundleName, remainingJobOffers, canRedeem};
    this.vueState.pricingPlan = bundleName;
  }

  setJobOfferApplicationEmail(applicationEmail: string) {
    this.vueState.applicationEmail = applicationEmail;
  }

  setPaymentSummary(summary: PaymentSummary): void {
    this.vueState.paymentSummary = summary;
  }

  setPaymentInvoiceCountries(countries: Country[]): void {
    this.vueState.invoiceCountries = countries;
  }

  setPaymentProcessing(processing: boolean): void {
    this.vueState.paymentProcessing = processing;
  }

  upload(upload: UploadAssets): void {
    this._upload = upload;
  }

  setVatIncluded(vatIncluded: boolean): void {
    this.vueState.paymentSummary!.vatIncluded = vatIncluded;
  }

  setVatIdState(state: VatIdState): void {
    this.vueState.paymentVatIdState = state;
  }

  setTagAutocomplete(tagAutocomplete: TagAutocomplete): void {
    this.tagAutocomplete = tagAutocomplete;
  }

  setJobOfferFavourite(jobOfferId: number, favourite: boolean): void {
    this.findJobOfferReactive(jobOfferId)!.isFavourite = favourite;
  }

  showValueProposition(jobOffer: JobOffer): void {
    this.vueState.vpVisibleFor = jobOffer;
  }

  hideValueProposition(): void {
    this.vueState.vpVisibleFor = null;
  }

  valuePropositionAccepted(
    event: ValuePropositionEvent,
    email?: string,
  ): void {
    this.viewListener!.valuePropositionAccepted(this.vueState.vpVisibleFor!, event, email);
  }

  mount(element: Element): void {
    const app = createApp({render: () => h(JobBoard, this.vueState)});
    const pinia = createPinia();
    app.use(pinia);
    const store = useBoardStore();
    app.provide(jobBoardServiceInjectKey, new JobBoardService(
      store,
      this.viewListener!,
      this.uiController,
      this.tagAutocomplete!,
      this._upload!,
    ));
    this.screens.useIn(app);
    app.mount(element);
  }
}

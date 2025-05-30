import {createApp, h, reactive} from 'vue';
import {BackendTag} from "../../backend";
import {JobOffer} from '../../jobBoard';
import {JobOfferFilter} from "../../jobOfferFilter";
import {LocationInput} from "../../location/LocationInput";
import {
  Country,
  InitiatePayment,
  JobOfferFilters,
  PaymentSummary,
  PlanBundleName,
  PricingPlan,
  SubmitJobOffer,
  UploadAssets,
  VatIdState,
} from "../../main";
import {PaymentNotification} from "../../paymentProvider/PaymentProvider";
import {PaymentStatus} from "../../paymentProvider/PaymentService";
import {Toast, View} from '../view';
import JobBoard from './JobBoard.vue';
import {JobBoardProperties} from "./JobBoardProperties";
import {emptyJobOfferFilter} from "./JobOffer/JobOfferFilters";
import {Policy} from "./screen/Policy";
import {RouteProperties, Screens} from "./screen/Screens";

export type Screen = 'home'|'edit'|'form'|'payment'|'pricing'|'show';

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
}

export interface NavigationListener {
  setScreen(screen: Screen, jobOfferId: number|null): void;
  showJobOfferForm(): void;
}

export interface FilterListener {
  filter(filter: JobOfferFilter): void;
  filterOnlyMine(onlyMine: boolean): void;
}

export interface PlanBundle {
  bundleName: PlanBundleName;
  remainingJobOffers: number;
  canRedeem: boolean;
}

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
}

export type CanEdit = (jobOfferId: number) => boolean;
export type PricingPlanSelected = () => boolean;

export type TagAutocomplete = (tagPrompt: string, result: TagAutocompleteResult) => void;
export type TagAutocompleteResult = (tags: BackendTag[]) => void;

export class VueUi {
  private readonly gate: Policy;
  private readonly screens: Screens;
  private readonly vueState: JobBoardProperties;
  private readonly filterListeners: FilterListener[] = [];
  private navigationListener: NavigationListener|null = null;
  private view: View|null = null;

  constructor(locationInput: LocationInput, isAuthenticated: boolean) {
    this.vueState = reactive<JobBoardProperties>({
      viewListener: null,
      tagAutocomplete: null,
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
      upload: null,
      applicationEmail: null,
      paymentSummary: null,
      paymentVatIdState: 'valid',
      invoiceCountries: null,
      locationInput,
      uiController: {
        showForm: this.showForm.bind(this),
        selectPlan: this.selectPlan.bind(this),
        navigate: this.navigate.bind(this),
        filter: this.filter.bind(this),
        filterOnlyMine: this.filterOnlyMine.bind(this),
        applyForJob: this.applyForJob.bind(this),
        showJobOffer: this.showJobOffer.bind(this),
        jobOfferUrl: this.jobOfferUrl.bind(this),
        resumePayment: this.resumePayment.bind(this),
      },
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
        this.vueState.viewListener!.resumePayment(jobOfferId);
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
    const listener: ViewListener = this.vueState.viewListener!;
    if (listener.assertUserAuthenticated()) {
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
    const jobOffer = this.findJobOffer(jobOfferId)!;
    if (jobOffer.applicationMode === 'external-ats') {
      window.open(jobOffer.applicationUrl, '_blank');
    } else {
      window.location.href = jobOffer.applicationUrl;
    }
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
    this.vueState.viewListener = viewListener;
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
    this.vueState.upload = upload;
  }

  setVatIncluded(vatIncluded: boolean): void {
    this.vueState.paymentSummary!.vatIncluded = vatIncluded;
  }

  setVatIdState(state: VatIdState): void {
    this.vueState.paymentVatIdState = state;
  }

  setTagAutocomplete(tagAutocomplete: TagAutocomplete): void {
    this.vueState.tagAutocomplete = tagAutocomplete;
  }

  mount(element: Element): void {
    const app = createApp({render: () => h(JobBoard, this.vueState)});
    this.screens.useIn(app);
    app.mount(element);
  }
}

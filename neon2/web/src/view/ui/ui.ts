import {createPinia} from "pinia";
import {createApp} from 'vue';
import {UploadAssets, ValuePropositionEvent, VatIdState} from "../../main";
import {JobBoardService} from "../../neon3/Apps/VueApp/Modules/JobBoard/JobBoardService";
import {BoardStore, useBoardStore} from "../../neon3/Apps/VueApp/Modules/JobBoard/store";
import {jobBoardServiceInjectKey} from "../../neon3/Apps/VueApp/Modules/JobBoard/vue";
import {LocationInput} from "../../neon3/Packages/Core/Application/LocationInput";
import {PaymentNotification} from "../../neon3/Packages/Core/Application/PaymentProvider";
import {Filter, FilterOptions} from "../../neon3/Packages/Feature/JobBoard/Application/filter";
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
  filter(filter: Filter): void;
  filterOnlyMine(onlyMine: boolean): void;
}

export type CanEdit = (jobOfferId: number) => boolean;
export type PricingPlanSelected = () => boolean;
export type TagAutocomplete = (tagPrompt: string, result: TagAutocompleteResult) => void;
export type TagAutocompleteResult = (tags: Tag[]) => void;

export class VueUi {
  private readonly gate: Policy;
  private readonly screens: Screens;
  private readonly filterListeners: FilterListener[] = [];
  private navigationListener: NavigationListener|null = null;
  private view: View|null = null;
  private viewListener: ViewListener|null = null;
  private tagAutocomplete: TagAutocomplete|null = null;
  private _upload: UploadAssets|null = null;
  private store: BoardStore|null = null;
  private app;

  constructor(private locationInput: LocationInput, isAuthenticated: boolean) {
    this.gate = new Policy(
      isAuthenticated,
      (jobOfferId: number): boolean => this.findJobOffer(jobOfferId)?.canEdit ?? false,
      () => this.store!.pricingPlan !== null,
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
    this.app = createApp(JobBoard);
    const pinia = createPinia();
    this.app.use(pinia);
    this.store = useBoardStore();
  }

  setView(view: View): void {
    this.view = view;
  }

  selectPlan(plan: PricingPlan): void {
    if (this.viewListener!.assertUserAuthenticated()) {
      this.store!.pricingPlan = plan;
      this.setScreen('form', null);
    }
  }

  setScreen(screen: Screen, jobOfferId: number|null): void {
    this.screens.navigate(screen, jobOfferId);
    window.scrollTo(0, 0);
  }

  // from main:

  setViewListener(viewListener: ViewListener): void {
    this.viewListener = viewListener;
  }

  setJobOfferFilters(filters: FilterOptions): void {
    this.store!.jobOfferFilters = filters;
  }

  setJobOfferFilter(filter: Filter): void {
    this.store!.jobOfferFilter = filter;
  }

  setPaymentNotification(notification: PaymentNotification): void {
    this.store!.paymentNotification = notification;
  }

  setPaymentStatus(status: PaymentStatus): void {
    this.store!.paymentStatus = status;
  }

  setJobOfferApplicationEmail(applicationEmail: string): void {
    this.store!.applicationEmail = applicationEmail;
  }

  setPaymentSummary(summary: PaymentSummary): void {
    this.store!.paymentSummary = summary;
  }

  setPaymentInvoiceCountries(countries: Country[]): void {
    this.store!.invoiceCountries = countries;
  }

  setPaymentProcessing(processing: boolean): void {
    this.store!.paymentProcessing = processing;
  }

  upload(upload: UploadAssets): void {
    this._upload = upload;
  }

  setVatIncluded(vatIncluded: boolean): void {
    this.store!.paymentSummary!.vatIncluded = vatIncluded;
  }

  setVatIdState(state: VatIdState): void {
    this.store!.paymentVatIdState = state;
  }

  setTagAutocomplete(tagAutocomplete: TagAutocomplete): void {
    this.tagAutocomplete = tagAutocomplete;
  }

  setJobOfferFavourite(jobOfferId: number, favourite: boolean): void {
    this.findJobOfferReactive(jobOfferId)!.isFavourite = favourite;
  }

  // from view

  setNavigationListener(navigationListener: NavigationListener): void {
    this.navigationListener = navigationListener;
  }

  addFilterListener(listener: FilterListener): void {
    this.filterListeners.push(listener);
  }

  setToast(toast: Toast|null): void {
    this.store!.toast = toast;
  }

  showValueProposition(jobOffer: JobOffer): void {
    this.store!.vpVisibleFor = jobOffer;
  }

  hideValueProposition(): void {
    this.store!.vpVisibleFor = null;
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.store!.jobOffers = jobOffers;
  }

  /**
   * This can only be run after ui create, before mount
   */
  setPlanBundle(bundleName: PlanBundleName, remainingJobOffers: number, canRedeem: boolean): void {
    this.store!.planBundle = {bundleName, remainingJobOffers, canRedeem};
    this.store!.pricingPlan = bundleName;
  }

  mount(element: Element): void {
    this.app.provide(jobBoardServiceInjectKey, new JobBoardService(this, this.view!, this.store!, this.screens, this.locationInput, this.viewListener!, this.tagAutocomplete!, this._upload!, this.filterListeners, this.navigationListener!));
    this.screens.useIn(this.app);
    this.app.mount(element);
  }

  private findJobOfferReactive(jobOfferId: number): JobOffer {
    const jobOffer = this.store!.jobOffers.find(o => o.id === jobOfferId);
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
      ' offers in view' + this.store!.jobOffers.length);
  }

  private findJobOffer(jobOfferId: number): JobOffer|null {
    return this.view!.findJobOffer(jobOfferId);
  }
}

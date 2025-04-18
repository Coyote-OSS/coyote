import {createApp, h, Reactive, reactive, VNode} from 'vue';
import {JobOffer} from '../../jobBoard';
import {InvoiceInformation, PlanBundleName, PricingPlan, SubmitJobOffer} from "../../main";
import {PaymentNotification} from "../../paymentProvider";
import {PaymentStatus} from "../../paymentService";
import {Toast} from '../view';
import JobBoard, {JobBoardProps, Screen} from './JobBoard.vue';

export {Screen} from './JobBoard.vue';

export interface ViewListener {
  createJob: (plan: PricingPlan, jobOffer: SubmitJobOffer) => void;
  updateJob: (jobOfferId: number, jobOffer: SubmitJobOffer) => void;
  payForJob: (id: number, invoiceInfo: InvoiceInformation) => void;
  redeemBundle: (jobOfferId: number) => void;
  managePaymentMethod: (action: 'mount'|'unmount', cssSelector?: string) => void;
}

export interface UserInterface {
  mount(cssSelector: string): void;
  setJobOffers(jobOffers: JobOffer[]): void;
  setToast(toast: Toast|null): void;
  addViewListener(listener: ViewListener): void;
  addNavigationListener(listener: NavigationListener): void;
  addSearchListener(listener: SearchListener): void;
  setScreen(screen: Screen): void;
  setCurrentJobOfferId(jobOfferId: number): void;
  setPaymentNotification(notification: PaymentNotification): void;
  setPaymentStatus(status: PaymentStatus): void;
  setPlanBundle(bundleName: PlanBundleName, remainingJobOffers: number, canRedeem: boolean): void;
}

export interface NavigationListener {
  setScreen(screen: Screen): void;
  showJobOfferForm(): void;
}

export type SearchListener = (searchPhrase: string) => void;

export interface PlanBundle {
  bundleName: PlanBundleName;
  remainingJobOffers: number;
  canRedeem: boolean;
}

export class VueUi implements UserInterface {
  private vueState: Reactive<JobBoardProps> = reactive<JobBoardProps>({
    jobOffers: [],
    toast: null,
    screen: 'home',
    currentJobOfferId: null,
    paymentNotification: null,
    paymentStatus: null,
    planBundle: null,
    pricingPlan: null,
  });
  private viewListeners: ViewListener[] = [];
  private navigationListeners: NavigationListener[] = [];
  private searchListeners: SearchListener[] = [];

  addViewListener(viewListener: ViewListener): void {
    this.viewListeners.push(viewListener);
  }

  addNavigationListener(navigationListener: NavigationListener): void {
    this.navigationListeners.push(navigationListener);
  }

  addSearchListener(listener: SearchListener): void {
    this.searchListeners.push(listener);
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this.vueState.jobOffers = jobOffers;
  }

  setScreen(screen: Screen): void {
    this.vueState.screen = screen;
  }

  setToast(toast: Toast|null): void {
    this.vueState.toast = toast;
  }

  setCurrentJobOfferId(jobOfferId: number|null): void {
    this.vueState.currentJobOfferId = jobOfferId;
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

  mount(cssSelector: string): void {
    const render = this.vueRender.bind(this);
    createApp({render}).mount(cssSelector);
  }

  private vueRender(): VNode {
    const that = this;
    return h(JobBoard, {
      ...this.vueState,
      onShowForm(): void {
        that.navigationListeners.forEach(listener => listener.showJobOfferForm());
      },
      onSelectPlan(plan: PricingPlan): void {
        that.vueState.pricingPlan = plan;
      },
      onCreate(plan: PricingPlan, jobOffer: SubmitJobOffer): void {
        that.viewListeners.forEach(listener => listener.createJob(plan, jobOffer));
      },
      onUpdate(jobOfferId: number, jobOffer: SubmitJobOffer): void {
        that.viewListeners.forEach(listener => listener.updateJob(jobOfferId, jobOffer));
      },
      onPay(jobOfferId: number, invoiceInfo: InvoiceInformation): void {
        that.viewListeners.forEach(listener => listener.payForJob(jobOfferId, invoiceInfo));
      },
      onRedeemBundle(jobOfferId: number): void {
        that.viewListeners.forEach(listener => listener.redeemBundle(jobOfferId));
      },
      onNavigate(screen: Screen, jobOfferId: number|null): void {
        that.navigationListeners.forEach(listener => listener.setScreen(screen));
        that.setCurrentJobOfferId(jobOfferId);
      },
      onSearch(searchPhrase: string): void {
        that.searchListeners.forEach(listener => listener(searchPhrase));
      },
      onMountCardInput(cssSelector: string): void {
        that.viewListeners.forEach(listener => listener.managePaymentMethod('mount', cssSelector));
      },
      onUnmountCardInput(): void {
        that.viewListeners.forEach(listener => listener.managePaymentMethod('unmount'));
      },
    });
  }
}

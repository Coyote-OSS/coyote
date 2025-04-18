import {BackendJobOffer, JobBoardBackend} from "./backend";
import {JobBoard, JobOffer} from './jobBoard';
import {JobOfferPayments} from "./jobOfferPayments";
import {PaymentNotification, PaymentProvider, Stripe, TestPaymentProvider} from "./paymentProvider";
import {PaymentService, PaymentStatus} from "./paymentService";
import {PlanBundle} from "./planBundle";
import {VueUi} from './view/ui/ui';
import {View} from "./view/view";

const view = new View(new VueUi());
const board = new JobBoard((jobOffers: JobOffer[]): void => view.setJobOffers(jobOffers));
const backend = new JobBoardBackend();
const paymentProvider: PaymentProvider = backend.testMode()
  ? new TestPaymentProvider()
  : new Stripe('pk_test_51RBWn0Rf5n1iRahJpeSAwkiae2lwuhS2BCH18TKWUOsE9WIn5SA6kojudAolQEcKuFjUTOwNBFNuzM89bQqctAnz00ciq6x7UN');
const payment = new PaymentService(backend, paymentProvider);
const payments = new JobOfferPayments();
const planBundle = new PlanBundle();

export type PlanBundleName = 'strategic'|'growth'|'scale';
export type PricingPlan = 'free'|'premium'|PlanBundleName;

export interface SubmitJobOffer {
  title: string;
  description: string;
  companyName: string;
}

view.addEventListener({
  createJob(pricingPlan: PricingPlan, jobOffer: SubmitJobOffer): void {
    backend.addJobOffer(pricingPlan, jobOffer, (jobOffer: BackendJobOffer): void => {
      const {id, title, expiresInDays, status, description, companyName} = jobOffer;
      board.jobOfferCreated({id, title, description, expiresInDays, status, companyName});
      if (pricingPlan === 'free') {
        view.jobOfferCreatedFree();
      } else {
        payments.addJobOffer({jobOfferId: id, paymentId: jobOffer.paymentId, pricingPlan});
        view.jobOfferCreatedRequirePayment(id);
      }
    });
  },
  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    backend.updateJobOffer(jobOfferId, jobOffer, (): void => {
      board.jobOfferUpdated(jobOfferId, jobOffer.title, jobOffer.description, jobOffer.companyName);
      view.jobOfferEdited();
    });
  },
  payForJob(jobOfferId: number): void {
    payment.initiatePayment(payments.paymentId(jobOfferId));
  },
  redeemBundle(jobOfferId: number): void {
    backend.publishJobOfferUsingBundle(jobOfferId).then(() => {
      board.jobOfferPaid(jobOfferId);
      view.planBundleUsed();
      view.jobOfferPaid();
      planBundle.decrease();
    });
  },
  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void {
    if (action === 'mount') {
      paymentProvider.mountCardInput(cssSelector!);
    } else {
      paymentProvider.unmountCardInput();
    }
  },
});

payment.addEventListener({
  notificationReceived(notification: PaymentNotification): void {
    view.setPaymentNotification(notification);
  },
  statusChanged(paymentId: string, status: PaymentStatus): void {
    view.setPaymentStatus(status);
    if (status === 'paymentComplete') {
      board.jobOfferPaid(payments.jobOfferId(paymentId));
      const pricingPlan = payments.pricingPlan(paymentId);
      if (pricingPlan !== 'premium') {
        planBundle.set(pricingPlan, remainingJobOffers(pricingPlan));
      }
      view.jobOfferPaid();
    }
  },
});

planBundle.addListener(function (plan: PricingPlan, remainingJobOffers: number): void {
  view.setPlanBundle(plan, remainingJobOffers);
});

function remainingJobOffers(planBundle: PlanBundleName): number {
  if (planBundle === 'strategic') {
    return 2;
  }
  if (planBundle === 'growth') {
    return 4;
  }
  if (planBundle === 'scale') {
    return 19;
  }
  throw new Error('Failed to set remaining job offers for a pricing plan.');
}

const bundle = backend.initialPlanBundle();
if (bundle.hasBundle) {
  planBundle.set(bundle.planBundleName, bundle.remainingJobOffers);
}

backend.initialJobOffers()
  .forEach(offer => board.jobOfferCreated({...offer}));

view.mount('#neonApplication');

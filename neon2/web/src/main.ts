import {BackendJobOffer, JobBoardBackend} from "./backend";
import {JobBoard, JobOffer} from './jobBoard';
import {JobOfferPayments} from "./jobOfferPayments";
import {PaymentNotification, PaymentProvider, Stripe, TestPaymentProvider} from "./paymentProvider";
import {PaymentService, PaymentStatus} from "./paymentService";
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

view.addEventListener({
  createJob(title: string, plan: 'free'|'paid'): void {
    backend.addJobOffer(title, plan, (jobOffer: BackendJobOffer): void => {
      const {id, title, expiresInDays, status} = jobOffer;
      payments.addJobOffer({jobOfferId: id, paymentId: jobOffer.paymentId});
      board.jobOfferCreated({id, title, expiresInDays, status});
      view.jobOfferCreated(id, plan);
    });
  },
  updateJob(id: number, title: string): void {
    const jobOfferId = id;
    backend.updateJobOffer(jobOfferId, title, (): void => {
      board.jobOfferUpdated(jobOfferId, title);
      view.jobOfferEdited();
    });
  },
  payForJob(jobOfferId: number): void {
    payment.initiatePayment(payments.paymentId(jobOfferId));
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
      view.jobOfferPaid();
    }
  },
});

backend.initialJobOffers()
  .forEach(offer => board.jobOfferCreated({...offer}));

view.mount('#neonApplication');

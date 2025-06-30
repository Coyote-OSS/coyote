import {InvoiceInformation, PricingPlan} from "../../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../../Domain/JobBoard/JobOffer";
import {PaymentIntent} from "../../../Domain/JobBoard/PaymentIntent";
import {PaymentStatus} from "../../../Domain/JobBoard/PaymentStatus";
import {PreparedPayment} from "./PreparedPayment";
import {SubmitJobOffer} from "./SubmitJobOffer";

export interface JobBoardApi {
  addJobOffer(
    pricingPlan: PricingPlan,
    jobOffer: SubmitJobOffer,
    created: BiConsumer<JobOffer, PaymentIntent|null>,
  ): void;
  updateJobOffer(id: number, jobOffer: SubmitJobOffer, updated: Runnable): void;
  markJobOfferAsFavourite(jobOfferId: number, favourite: boolean): Promise<void>;
  preparePayment(userId: number, paymentId: string, invoiceInfo: InvoiceInformation): Promise<PreparePaymentResponse>;
  publishJobOfferUsingBundle(jobOfferId: number, userId: number): Promise<void>;
  fetchPaymentStatus(paymentId: string): Promise<PaymentStatus>;
}

export interface PreparePaymentResponse {
  status: 'success'|'failedInvalidVatId';
  preparedPayment?: PreparedPayment;
}

type Runnable = () => void;
type BiConsumer<A, B> = (arg1: A, arg2: B) => void;

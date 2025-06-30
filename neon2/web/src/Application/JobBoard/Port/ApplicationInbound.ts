import {Country} from "../../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../../Domain/JobBoard/JobOffer";
import {JobOfferPaymentIntent} from "../../../Domain/JobBoard/JobOfferPaymentIntent";
import {PlanBundle} from "../../../Domain/JobBoard/PlanBundle";

export interface ApplicationInbound {
  testMode(): boolean;

  initialJobOffers(): JobOffer[];
  initialPlanBundle(): PlanBundle;

  jobOfferApplicationEmail(): string;
  jobOfferPayments(): JobOfferPaymentIntent[];
  paymentInvoiceCountries(): Country[];

  stripeKey(): string|null;
  acceptanceTagNames(): string[];

  csrfToken(): string;
  isAuthenticated(): boolean;
  userId(): number;
}

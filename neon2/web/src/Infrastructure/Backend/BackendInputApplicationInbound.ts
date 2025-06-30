import {ApplicationInbound} from "../../Application/JobBoard/Port/ApplicationInbound";
import {Country} from "../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../Domain/JobBoard/JobOffer";
import {JobOfferPaymentIntent} from "../../Domain/JobBoard/JobOfferPaymentIntent";
import {PlanBundle} from "../../Domain/JobBoard/PlanBundle";
import {BackendInput} from "./BackendInput";
import {toJobOffer} from "./toJobOffer";

export class BackendInputApplicationInbound implements ApplicationInbound {
  constructor(private backendInput: BackendInput) {}

  initialJobOffers(): JobOffer[] {
    return this.backendInput.jobOffers.map(o => toJobOffer(o));
  }

  jobOfferPayments(): JobOfferPaymentIntent[] {
    return this.backendInput
      .jobOffers
      .filter(jobOffer => jobOffer.payment !== null)
      .map(jobOffer => {
        return {
          jobOfferId: jobOffer.id,
          paymentIntent: jobOffer.payment!,
        };
      });
  }

  initialPlanBundle(): PlanBundle {
    return this.backendInput.planBundle;
  }

  userId(): number {
    if (!this.backendInput.userId) {
      throw new Error('Failed to retrieve userId of an unauthenticated user.');
    }
    return this.backendInput.userId;
  }

  jobOfferApplicationEmail(): string {
    return this.backendInput.jobOfferApplicationEmail ?? '';
  }

  testMode(): boolean {
    return this.backendInput.testMode;
  }

  stripeKey(): string|null {
    return this.backendInput.stripePublishableKey;
  }

  paymentInvoiceCountries(): Country[] {
    return this.backendInput.paymentInvoiceCountries;
  }

  isAuthenticated(): boolean {
    return this.backendInput.userId !== null;
  }

  csrfToken(): string {
    return this.backendInput.csrfToken;
  }

  acceptanceTagNames(): string[] {
    return this.backendInput.acceptanceTagNames;
  }
}

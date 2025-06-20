import {BackendApi} from "./BackendApi";
import {BackendInput, BackendPlanBundle} from "./backendInput";
import {toJobOffer} from "./toJobOffer";
import {JobOffer} from "../../Feature/JobBoard/Domain/JobOffer";
import {Country, Tag} from "../../Feature/JobBoard/Domain/Model";
import {JobOfferPaymentIntent} from "../../Feature/JobBoard/JobBoard";

export class JobBoardBackend {
  private backendInput: BackendInput = window.backendInput;

  constructor(private backendApi: BackendApi) {}

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

  initialPlanBundle(): BackendPlanBundle {
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

  async tagsAutocomplete(prompt: string): Promise<Tag[]> {
    if (this.testMode()) {
      return this.backendInput.acceptanceTagNames.map(tagName => {
        return {tagName, title: null, timesUsed: 0};
      });
    }
    return this.backendApi.tagsAutocomplete(prompt);
  }

  csrfToken(): string {
    return this.backendInput.csrfToken;
  }
}

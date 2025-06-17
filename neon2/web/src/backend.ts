import {Country, JobOfferTag} from "./main";
import {BackendApi} from "./neon3/Packages/Core/Backend/BackendApi";
import {
  BackendInput,
  BackendJobOffer,
  BackendPlanBundle,
  BackendPreparedPayment,
} from "./neon3/Packages/Core/Backend/backendInput";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Application/JobOffer";
import {Tag} from "./neon3/Packages/Feature/JobBoard/Domain/Model";
import {parseWorkMode} from "./neon3/Packages/Feature/JobBoard/Domain/workMode";
import {JobOfferPaymentIntent} from "./neon3/Packages/Feature/JobBoard/JobBoard";

export class JobBoardBackend {
  private backendInput: BackendInput = window.backendInput;

  constructor(private backendApi: BackendApi) {}

  initialJobOffers(): BackendJobOffer[] {
    return this.backendInput.jobOffers;
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

  async uploadLogoReturnUrl(file: File): Promise<string> {
    const formData = new FormData();
    formData.append('logo', file);
    return fetch('/Firma/Logo', {
      method: 'POST',
      body: formData,
      headers: {'X-CSRF-TOKEN': this.backendInput.csrfToken},
    })
      .then(response => response.json())
      .then(uploadedImage => uploadedImage.url);
  }

  async uploadAssetReturnUrl(file: File): Promise<string> {
    const formData = new FormData();
    formData.append('asset', file);
    return fetch('/assets', {
      method: 'POST',
      body: formData,
      headers: {'X-CSRF-TOKEN': this.backendInput.csrfToken},
    })
      .then(response => response.json())
      .then(uploadedImage => uploadedImage.url);
  }

  isAuthenticated(): boolean {
    return this.backendInput.userId !== null;
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

  async tagsAutocomplete(prompt: string): Promise<Tag[]> {
    if (this.testMode()) {
      return this.backendInput.acceptanceTagNames.map(tagName => {
        return {tagName, title: null, timesUsed: 0};
      });
    }
    return this.backendApi.tagsAutocomplete(prompt);
  }
}

export function toJobOffer(jobOffer: BackendJobOffer): JobOffer {
  const {fields, ...operationalFields} = jobOffer;
  return {
    ...operationalFields,
    ...fields,
    workMode: parseWorkMode(jobOffer.fields.workModeRemoteRange),
    tags: jobOfferTags(jobOffer),
  };
}

function jobOfferTags(jobOffer: BackendJobOffer): JobOfferTag[] {
  return jobOffer.fields.tagNames.map((tagName: string, index: number): JobOfferTag => {
    return {
      tagName,
      priority: jobOffer.fields.tagPriorities[index],
    };
  });
}

export interface PreparePaymentResponse {
  status: 'success'|'failedInvalidVatId';
  preparedPayment?: BackendPreparedPayment;
}

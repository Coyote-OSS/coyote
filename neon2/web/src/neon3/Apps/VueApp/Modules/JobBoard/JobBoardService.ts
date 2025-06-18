import {Screens} from "../../../../../Screens";
import {LocationDisplay} from "../../../../Packages/Core/Application/LocationDisplay";
import {LocationInput, LocationListener} from "../../../../Packages/Core/Application/LocationInput";
import {PaymentProvider} from "../../../../Packages/Core/Application/PaymentProvider";
import {BackendApi} from "../../../../Packages/Core/Backend/BackendApi";
import {BackendImageApi} from "../../../../Packages/Core/Backend/BackendImageApi";
import {BackendPaymentIntent} from "../../../../Packages/Core/Backend/backendInput";
import {JobBoardBackend} from "../../../../Packages/Core/Backend/JobBoardBackend";
import {isVatIncluded} from "../../../../Packages/Core/Domain/vat";
import {Filter} from "../../../../Packages/Feature/JobBoard/Application/filter";
import {FilterRepository} from "../../../../Packages/Feature/JobBoard/Application/FilterRepository";
import {JobOfferFilterService} from "../../../../Packages/Feature/JobBoard/Application/JobOfferFilterService";
import {JobOfferRepository} from "../../../../Packages/Feature/JobBoard/Application/JobOfferRepository";
import {InitiatePayment, SubmitJobOffer} from "../../../../Packages/Feature/JobBoard/Application/Model";
import {PaymentIntentRepository} from "../../../../Packages/Feature/JobBoard/Application/PaymentIntentRepository";
import {PaymentService} from "../../../../Packages/Feature/JobBoard/Application/PaymentService";
import {PlanBundleRepository} from "../../../../Packages/Feature/JobBoard/Application/PlanBundleRepository";
import {
  TagAutocomplete,
  TagAutocompleteResult,
} from "../../../../Packages/Feature/JobBoard/Application/TagAutocomplete";
import {bundleSize, remainingJobOffers} from "../../../../Packages/Feature/JobBoard/Domain/bundleSize";
import {JobOffer} from "../../../../Packages/Feature/JobBoard/Domain/JobOffer";
import {PaymentStatus, PaymentSummary, PricingPlan} from "../../../../Packages/Feature/JobBoard/Domain/Model";
import {Screen} from "../../../../Packages/Feature/JobBoard/Presenter/Model";
import {EventMetadata, ValuePropositionEvent} from "../../../../Packages/Feature/Vp/Model";
import {BoardStore} from "./store";
import {ViewModel} from "./ViewModel";

export class JobBoardService {
  private readonly filterRepo: FilterRepository = new FilterRepository();
  private readonly filterService: JobOfferFilterService;

  constructor(
    private readonly viewModel: ViewModel,
    private readonly store: BoardStore,
    private readonly screens: Screens,
    private readonly locationInput: LocationInput,
    private readonly locationDisplay: LocationDisplay,
    private readonly tagAutocomplete: TagAutocomplete,
    private readonly backendImageApi: BackendImageApi,
    private readonly backendApi: BackendApi,
    private readonly backend: JobBoardBackend,
    private readonly jobOffersRepo: JobOfferRepository,
    private readonly planBundleRepo: PlanBundleRepository,
    private readonly paymentIntents: PaymentIntentRepository,
    private readonly payments: PaymentService,
    private readonly paymentProvider: PaymentProvider,
  ) {
    this.filterService = new JobOfferFilterService(jobOffersRepo);
  }

  paymentStatusChanged(paymentId: string, status: PaymentStatus): void {
    this.viewModel.setPaymentStatus(status);
    if (status === 'paymentComplete') {
      this.jobOffersRepo.updateJobOfferPublished(this.paymentIntents.jobOfferId(paymentId));
      this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
      const pricingPlan = this.paymentIntents.pricingPlan(paymentId);
      if (pricingPlan !== 'premium') {
        this.planBundleRepo.set(pricingPlan, remainingJobOffers(pricingPlan));
      }
      this.screens.navigate('home', null);
    }
  }

  initJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffersRepo.setJobOffers(jobOffers);
    this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
  }

  redeemBundle(jobOfferId: number): void {
    this.backendApi
      .publishJobOfferUsingBundle(jobOfferId, this.backend.userId())
      .then(() => {
        this.jobOffersRepo.updateJobOfferPublished(jobOfferId);
        this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
        this.viewModel.notifyPlanBundleUsed();
        this.screens.navigate('home', null);
        this.planBundleRepo.decrease();
      });
  }

  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    this.backendApi.updateJobOffer(jobOfferId, jobOffer, (): void => {
      this.jobOffersRepo.updateJobOffer(jobOfferId, jobOffer);
      this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
      this.viewModel.notifyJobOfferEdited(jobOfferId);
      this.screens.navigate('home', null);
    });
  }

  createJob(plan: PricingPlan, jobOffer: SubmitJobOffer): void {
    this.backendApi
      .addJobOffer(
        plan,
        jobOffer,
        (jobOffer: JobOffer, payment: BackendPaymentIntent|null): void => {
          this.jobOffersRepo.insertFirst(jobOffer);
          this.viewModel.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
          if (plan === 'free') {
            this.viewModel.notifyJobOfferCreatedFree(jobOffer.id);
            this.screens.navigate('home', null);
          } else {
            this.paymentIntents.addJobOffer({jobOfferId: jobOffer.id, paymentIntent: payment!});
            this.viewModel.notifyJobOfferCreatedRequirePayment(
              jobOffer.id,
              this.paymentSummary(jobOffer.id));
            this.screens.navigate('payment', jobOffer.id);
          }
        });
  }

  applyForJob(jobOfferId: number): void {
    this.viewModel.showValueProposition(this.jobOffersRepo.findJobOffer(jobOfferId)!);
  }

  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    this.viewModel.setJobOfferFavourite(jobOfferId, favourite);
    this.backendApi.markJobOfferAsFavourite(jobOfferId, favourite);
  }

  selectPlan(plan: PricingPlan): void {
    if (this.backend.isAuthenticated()) {
      this.viewModel.notifyPlanSelected(plan);
      this.screens.navigate('form', null);
    } else {
      window.location.href = '/Login';
    }
  }

  managePaymentMethod(action: 'mount'|'unmount', cssSelector?: string): void {
    if (action === 'mount') {
      this.paymentProvider.mountCardInput((cssSelector)!);
    } else {
      this.paymentProvider.unmountCardInput();
    }
  }

  payForJob(payment: InitiatePayment): void {
    this.payments.initiatePayment(
      this.paymentIntents.paymentId(payment.jobOfferId),
      payment.invoiceInfo,
      payment.paymentMethod);
  }

  vatDetailsChanged(countryCode: string, vatId: string): void {
    this.viewModel.notifyVatIncludedChanged(isVatIncluded(countryCode, vatId));
  }

  resumePayment(jobOfferId: number): void {
    this.viewModel.initRequirePayment(this.paymentSummary(jobOfferId));
  }

  private paymentSummary(jobOfferId: number): PaymentSummary {
    const payment = this.paymentIntents.jobOfferPayment(jobOfferId);
    return {
      bundleSize: bundleSize(payment.paymentPricingPlan),
      basePrice: payment.paymentPriceBase,
      vat: payment.paymentPriceVat,
      vatIncluded: true,
    };
  }

  valuePropositionAccepted(
    event: ValuePropositionEvent,
    email?: string,
  ): void {
    const result = this.vpEvent(event, {jobOfferId: this.store!.vpVisibleFor!.id, email});
    if (event === 'vpDeclined' || event === 'vpApply') {
      this.viewModel.hideValueProposition();
      result.finally(() => this.jobOfferApply(this.store!.vpVisibleFor!));
    }
  }

  private jobOfferApply(jobOffer: JobOffer): void {
    if (jobOffer.applicationMode === 'external-ats') {
      window.open(jobOffer.applicationUrl, '_blank');
    } else {
      window.location.href = jobOffer.applicationUrl;
    }
  }

  vpEvent(eventName: string, metadata: EventMetadata): Promise<void> {
    return this.backendApi.event({eventName, metadata});
  }

  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void {
    this.locationDisplay.mount(element, latitude, longitude);
  }

  showJob(jobOfferId: number): void {
    this.screens.showJobOffer(this.jobOffersRepo.findJobOffer(jobOfferId)!);
  }

  showForm(): void {
    if (this.planBundleRepo.canRedeem()) {
      this.screens.navigate('form', null);
    } else {
      this.screens.navigate('pricing', null);
    }
  }

  filter(filter: Filter): void {
    this.filterRepo.setFilter(filter);
    this.store.jobOfferFilter = filter;

    this.store.jobOffers = this.filterService.filter(this.filterRepo);
  }

  filterOnlyMine(onlyMine: boolean): void {
    this.filterRepo.setFilterOnlyMine(onlyMine);

    this.store.jobOffers = this.filterService.filter(this.filterRepo);
  }

  navigate(screen: Screen, jobOfferId: number|null): void {
    this.store.toast = null;
    this.screens.navigate(screen, jobOfferId);
  }

  findJobOffer(jobOfferId: number): JobOffer|null {
    return this.jobOffersRepo.findJobOffer(jobOfferId);
  }

  /** @deprecated */
  jobOfferUrl(jobOffer: JobOffer): string {
    return this.screens.jobOfferUrl(jobOffer);
  }

  promptTagAutocomplete(tagPrompt: string, result: TagAutocompleteResult): void {
    this.tagAutocomplete.prompt(tagPrompt, result);
  }

  uploadLogo(file: File): Promise<string> {
    return this.backendImageApi.uploadLogoReturnUrl(file);
  }

  uploadAsset(file: File): Promise<string> {
    return this.backendImageApi.uploadAssetReturnUrl(file);
  }

  mountLocationInput(input: HTMLInputElement, listener: LocationListener): void {
    this.locationInput.mount(input, listener);
  }
}

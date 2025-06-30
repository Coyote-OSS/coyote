import {Filter} from "../../../Application/JobBoard/filter";
import {FilterRepository} from "../../../Application/JobBoard/FilterRepository";
import {JobOfferFilterService} from "../../../Application/JobBoard/JobOfferFilterService";
import {JobOfferRepository} from "../../../Application/JobBoard/JobOfferRepository";
import {InitiatePayment} from "../../../Application/JobBoard/Model";
import {PaymentIntentRepository} from "../../../Application/JobBoard/PaymentIntentRepository";
import {PaymentService} from "../../../Application/JobBoard/PaymentService";
import {ApplicationInbound} from "../../../Application/JobBoard/Port/ApplicationInbound";
import {ImageHosting} from "../../../Application/JobBoard/Port/ImageHosting";
import {JobBoardApi} from "../../../Application/JobBoard/Port/JobBoardApi";
import {LocationDisplay} from "../../../Application/JobBoard/Port/LocationDisplay";
import {LocationInput, LocationListener} from "../../../Application/JobBoard/Port/LocationInput";
import {PaymentProvider} from "../../../Application/JobBoard/Port/PaymentProvider";
import {TagAutocomplete} from "../../../Application/JobBoard/Port/TagAutocomplete";
import {SubmitJobOffer} from "../../../Application/JobBoard/Port/SubmitJobOffer";
import {NavigationApi} from "../../../Application/Navigation/Port/NavigationApi";
import {bundleSize, remainingJobOffers} from "../../../Domain/JobBoard/bundleSize";
import {isVatIncluded} from "../../../Domain/JobBoard/isVatIncluded";
import {PaymentSummary, PaymentUpdatedStatus, PricingPlan, Tag} from "../../../Domain/JobBoard/JobBoard";
import {JobOffer} from "../../../Domain/JobBoard/JobOffer";
import {PaymentIntent} from "../../../Domain/JobBoard/PaymentIntent";
import {PlanBundleRepository} from "../../../Domain/JobBoard/PlanBundleRepository";
import {EventMetadata, ValuePropositionEvent} from "../../../Domain/ValueProp/Model";
import {JobBoardNavigator} from "./JobBoardNavigator";
import {JobBoardView} from "./JobBoardView";
import {ScreenName} from "./Model";

export class JobBoardService {
  private readonly filterRepo: FilterRepository = new FilterRepository();
  private readonly filterService: JobOfferFilterService;

  constructor(
    private readonly view: JobBoardView,
    private readonly navigator: JobBoardNavigator,
    private readonly locationInput: LocationInput,
    private readonly locationDisplay: LocationDisplay,
    private readonly tagAutocomplete: TagAutocomplete,
    private readonly imageHosting: ImageHosting,
    private readonly jobBoardApi: JobBoardApi,
    private readonly navigationApi: NavigationApi,
    private readonly inbound: ApplicationInbound,
    private readonly jobOffersRepo: JobOfferRepository,
    private readonly planBundleRepo: PlanBundleRepository,
    private readonly paymentIntents: PaymentIntentRepository,
    private readonly payments: PaymentService,
    private readonly paymentProvider: PaymentProvider,
  ) {
    this.filterService = new JobOfferFilterService(jobOffersRepo);
  }

  paymentStatusChanged(paymentId: string, status: PaymentUpdatedStatus): void {
    this.view.setPaymentStatus(status);
    if (status === 'paymentComplete') {
      this.jobOffersRepo.updateJobOfferPublished(this.paymentIntents.jobOfferId(paymentId));
      this.view.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
      const pricingPlan = this.paymentIntents.pricingPlan(paymentId);
      if (pricingPlan !== 'premium') {
        this.planBundleRepo.set(pricingPlan, remainingJobOffers(pricingPlan));
      }
      this.navigator.navigate('home');
    }
  }

  initJobOffers(jobOffers: JobOffer[]): void {
    this.jobOffersRepo.setJobOffers(jobOffers);
    this.view.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
  }

  redeemBundle(jobOfferId: number): void {
    this.jobBoardApi
      .publishJobOfferUsingBundle(jobOfferId, this.inbound.userId())
      .then(() => {
        this.jobOffersRepo.updateJobOfferPublished(jobOfferId);
        this.view.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
        this.view.notifyPlanBundleUsed();
        this.navigator.navigate('home');
        this.planBundleRepo.decrease();
      });
  }

  updateJob(jobOfferId: number, jobOffer: SubmitJobOffer): void {
    this.jobBoardApi.updateJobOffer(jobOfferId, jobOffer, (): void => {
      this.jobOffersRepo.updateJobOffer(jobOfferId, jobOffer);
      this.view.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
      this.view.notifyJobOfferEdited(jobOfferId);
      this.navigator.navigate('home');
    });
  }

  createJob(plan: PricingPlan, jobOffer: SubmitJobOffer): void {
    this.jobBoardApi
      .addJobOffer(
        plan,
        jobOffer,
        (jobOffer: JobOffer, payment: PaymentIntent|null): void => {
          this.jobOffersRepo.insertFirst(jobOffer);
          this.view.notifyJobOffersChanged(this.filterService.filter(this.filterRepo));
          if (plan === 'free') {
            this.view.notifyJobOfferCreatedFree(jobOffer.id);
            this.navigator.navigate('home');
          } else {
            this.paymentIntents.addJobOffer({jobOfferId: jobOffer.id, paymentIntent: payment!});
            this.view.notifyJobOfferCreatedRequirePayment(
              jobOffer.id,
              this.paymentSummary(jobOffer.id));
            this.navigator.navigate('payment', jobOffer.id);
          }
        });
  }

  applyForJob(jobOfferId: number): void {
    this.view.showValueProposition(this.jobOffersRepo.findJobOffer(jobOfferId)!);
  }

  markAsFavourite(jobOfferId: number, favourite: boolean): void {
    this.view.setJobOfferFavourite(jobOfferId, favourite);
    this.jobBoardApi.markJobOfferAsFavourite(jobOfferId, favourite);
  }

  selectPlan(plan: PricingPlan): void {
    if (this.inbound.isAuthenticated()) {
      this.view.notifyPlanSelected(plan);
      this.navigator.navigate('form');
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
    this.view.notifyVatIncludedChanged(isVatIncluded(countryCode, vatId));
  }

  resumePayment(jobOfferId: number): void {
    this.view.initRequirePayment(this.paymentSummary(jobOfferId));
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
    email: string|undefined,
    jobOffer: JobOffer,
  ): void {
    const result = this.vpEvent(event, {jobOfferId: jobOffer.id, email});
    if (event === 'vpDeclined' || event === 'vpApply') {
      this.view.hideValueProposition();
      result.finally(() => this.jobOfferApply(jobOffer!));
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
    return this.navigationApi.event({eventName, metadata});
  }

  mountLocationDisplay(element: HTMLElement, latitude: number, longitude: number): void {
    this.locationDisplay.mount(element, latitude, longitude);
  }

  showJob(jobOfferId: number): void {
    this.navigator.showJobOffer(this.jobOffersRepo.findJobOffer(jobOfferId)!);
  }

  showForm(): void {
    if (this.planBundleRepo.canRedeem()) {
      this.navigator.navigate('form');
    } else {
      this.navigator.navigate('pricing');
    }
  }

  filter(filter: Filter): void {
    this.filterRepo.setFilter(filter);
    this.view.setJobOffers(this.filterService.filter(this.filterRepo));
    this.view.setFilter(filter);
  }

  filterOnlyMine(onlyMine: boolean): void {
    this.filterRepo.setFilterOnlyMine(onlyMine);
    this.view.setJobOffers(this.filterService.filter(this.filterRepo));
  }

  navigate(screen: ScreenName, jobOfferId: number|null): void {
    this.view.clearToast();
    if (jobOfferId === null) {
      this.navigator.navigate(screen);
    } else {
      this.navigator.navigate(screen, jobOfferId);
    }
  }

  findJobOffer(jobOfferId: number): JobOffer|null {
    return this.jobOffersRepo.findJobOffer(jobOfferId);
  }

  jobOfferUrl(jobOffer: JobOffer): string {
    return this.navigator.jobOfferUrl(jobOffer);
  }

  promptTagAutocomplete(tagPrompt: string): Promise<Tag[]> {
    return this.tagAutocomplete.prompt(tagPrompt);
  }

  uploadLogo(file: File): Promise<string> {
    return this.imageHosting.uploadLogoReturnUrl(file);
  }

  uploadAsset(file: File): Promise<string> {
    return this.imageHosting.uploadAssetReturnUrl(file);
  }

  mountLocationInput(input: HTMLInputElement, listener: LocationListener): void {
    this.locationInput.mount(input, listener);
  }
}

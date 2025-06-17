import {App} from "vue";
import JobOfferPricing from "./JobOffer/screen/JobOfferPricing.vue";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import JobOfferCreate from "./JobOffer/screen/JobOfferCreate.vue";
import JobOfferEdit from "./JobOffer/screen/JobOfferEdit.vue";
import JobOfferHome from "./JobOffer/screen/JobOfferHome.vue";
import JobOfferPaymentScreen from "./JobOffer/screen/JobOfferPaymentScreen.vue";
import JobOfferShowScreen from "./JobOffer/screen/JobOfferShowScreen.vue";
import {Screen} from "./ui";
import {Policy} from "./Policy";

import {Router} from "./Router";

export class Screens {
  private router: Router;

  constructor(listener: ScreenListener, policy: Policy) {
    this.router = new Router(listener);
    this.addScreens();
    this.router.addDefaultScreen('home');
    this.router.before((screen: Screen, jobOfferId: number|null): Screen|null => {
      if (screen === 'form' && !policy.createCreateJobOffer()) {
        return 'pricing';
      }
      if (screen === 'edit' && !policy.canEditJobOffer(jobOfferId!)) {
        return 'home';
      }
      if (screen === 'payment') {
        listener.resumePayment(jobOfferId!);
      }
      return null;
    });
  }

  private addScreens(): void {
    this.router.addScreen(JobOfferHome, 'home', '/Job');
    this.router.addScreen(JobOfferShowScreen, 'show', '/Job/:slug/:id');
    this.router.addScreen(JobOfferPricing, 'pricing', '/Job/pricing');
    this.router.addScreen(JobOfferCreate, 'form', '/Job/new');
    this.router.addScreen(JobOfferEdit, 'edit', '/Job/:id/edit');
    this.router.addScreen(JobOfferPaymentScreen, 'payment', '/Job/:id/payment');
  }

  navigate(screen: Screen, jobOfferId: number|null): void {
    this.router.navigate(screen, {id: jobOfferId});
  }

  showJobOffer(jobOffer: JobOffer): void {
    this.router.navigate('show', {
      id: jobOffer.id,
      slug: jobOffer.slug,
    });
  }

  jobOfferUrl(jobOffer: JobOffer): string {
    const {slug, id} = jobOffer;
    return `/Job/${slug}/${id}`;
  }

  useIn(app: App): void {
    this.router.useIn(app);
  }
}

export interface ScreenListener {
  routeProperties(jobOfferId: number|null): RouteProperties;
  resumePayment(jobOfferId: number): void;
}

export interface RouteProperties {
  routeJobOfferId: number|null;
  /** @deprecated properties are not reactive */
  routeJobOffer: JobOffer|null;
}

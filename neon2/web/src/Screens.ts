import {App} from "vue";
import JobOfferCreate from "./JobOffer/screen/JobOfferCreate.vue";
import JobOfferEdit from "./JobOffer/screen/JobOfferEdit.vue";
import JobOfferHome from "./JobOffer/screen/JobOfferHome.vue";
import JobOfferPaymentScreen from "./JobOffer/screen/JobOfferPaymentScreen.vue";
import JobOfferPricing from "./JobOffer/screen/JobOfferPricing.vue";
import JobOfferShowScreen from "./JobOffer/screen/JobOfferShowScreen.vue";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {Policy} from "./Policy";

import {Router} from "./Router";
import {Screen, ViewListener} from "./ui";

export class Screens {
  private router: Router;

  constructor(
    viewListener: ViewListener,
    policy: Policy,
  ) {
    this.router = new Router();
    this.router.addScreen(JobOfferHome, 'home', '/Job');
    this.router.addScreen(JobOfferShowScreen, 'show', '/Job/:slug/:id');
    this.router.addScreen(JobOfferPricing, 'pricing', '/Job/pricing');
    this.router.addScreen(JobOfferCreate, 'form', '/Job/new');
    this.router.addScreen(JobOfferEdit, 'edit', '/Job/:id/edit');
    this.router.addScreen(JobOfferPaymentScreen, 'payment', '/Job/:id/payment');
    this.router.addDefaultScreen('home');
    this.router.onNavigate((screen: Screen, jobOfferId: number|null): Screen|null => {
      if (screen === 'form' && !policy.createCreateJobOffer()) {
        return 'pricing';
      }
      if (screen === 'edit' && !policy.canEditJobOffer(jobOfferId!)) {
        return 'home';
      }
      if (screen === 'payment') {
        viewListener.resumePayment(jobOfferId!);
      }
      return null;
    });
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

export interface RouteProperties {
  routeJobOfferId: number|null;
}

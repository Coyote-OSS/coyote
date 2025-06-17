import {App} from "vue";
import JobOfferCreate from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferCreate.vue";
import JobOfferEdit from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferEdit.vue";
import JobOfferHome from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferHome.vue";
import JobOfferPaymentScreen from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferPaymentScreen.vue";
import JobOfferPricing from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferPricing.vue";
import JobOfferShowScreen from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferShowScreen.vue";
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
    this.router.addScreen(JobOfferCreate, 'form', '/Job/new', () => {
      if (!policy.createCreateJobOffer()) {
        return 'pricing';
      }
      return null;
    });
    this.router.addScreen(JobOfferEdit, 'edit', '/Job/:id/edit', params => {
      if (!policy.canEditJobOffer(Number(params.id))) {
        return 'home';
      }
      return null;
    });
    this.router.addScreen(JobOfferPaymentScreen, 'payment', '/Job/:id/payment');
    this.router.addDefaultScreen('home');
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

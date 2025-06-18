import {App} from "vue";
import JobOfferCreate from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferCreate.vue";
import JobOfferEdit from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferEdit.vue";
import JobOfferHome from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferHome.vue";
import JobOfferPaymentScreen from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferPaymentScreen.vue";
import JobOfferPricing from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferPricing.vue";
import JobOfferShowScreen from "./neon3/Apps/VueApp/Modules/JobBoard/View/JobOfferShowScreen.vue";
import {JobOffer} from "./neon3/Packages/Feature/JobBoard/Domain/JobOffer";
import {Screen} from "./neon3/Packages/Feature/JobBoard/Presenter/Model";
import {Policy} from "./neon3/Packages/Feature/JobBoard/Application/Policy";
import {Router} from "./Router";

export class Screens {
  private router: Router<Screen>;

  constructor(policy: Policy) {
    this.router = new Router<Screen>();
    this.router.addRoute(JobOfferHome, 'home', '/Job');
    this.router.addRoute(JobOfferShowScreen, 'show', '/Job/:slug/:id');
    this.router.addRoute(JobOfferPricing, 'pricing', '/Job/pricing');
    this.router.addRoute(JobOfferCreate, 'form', '/Job/new', () => {
      if (!policy.createJobOffer()) {
        return 'pricing';
      }
      return null;
    });
    this.router.addRoute(JobOfferEdit, 'edit', '/Job/:id/edit', params => {
      if (!policy.editJobOffer(Number(params.id))) {
        return 'home';
      }
      return null;
    });
    this.router.addRoute(JobOfferPaymentScreen, 'payment', '/Job/:id/payment');
    this.router.addDefaultRoute('home');
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

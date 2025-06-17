import {ScreenName} from "../../../../Apps/VueApp/Modules/JobBoard/Model";
import {Router} from "../../../Core/Application/Router";
import {JobOffer} from "../Domain/JobOffer";
import {Policy} from "./Policy";

export class JobBoardNavigator {
  constructor(
    private router: Router<ScreenName>,
    private policy: Policy,
  ) {
    this.addRoutes();
    this.router.addDefaultRoute('home');
  }

  private addRoutes(): void {
    this.router.addRoute('home');
    this.router.addRoute('show');
    this.router.addRoute('pricing');
    this.router.addRoute('form', () => {
      if (!this.policy.createJobOffer()) {
        return 'pricing';
      }
      return null;
    });
    this.router.addRoute('edit', params => {
      if (!this.policy.editJobOffer(Number(params.id))) {
        return 'home';
      }
      return null;
    });
    this.router.addRoute('payment');
  }

  navigate(screen: ScreenName, jobOfferId?: number): void {
    this.router.navigate(screen, {id: jobOfferId});
  }

  showJobOffer(jobOffer: JobOffer): void {
    this.router.navigate('show', {
      id: jobOffer.id,
      slug: jobOffer.slug,
    });
  }

  jobOfferUrl(jobOffer: JobOffer): string {
    return this.router.resolveUrl('show', {id: jobOffer.id, slug: jobOffer.slug});
  }
}

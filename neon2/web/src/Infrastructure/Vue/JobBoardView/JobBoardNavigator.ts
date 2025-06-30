import {JobOfferRepository} from "../../../Application/JobBoard/JobOfferRepository";
import {PricingPlanPort} from "../../../Application/JobBoard/Port/PricingPlanPort";
import {JobOffer} from "../../../Domain/JobBoard/JobOffer";
import {Router} from "../Router";
import {ScreenName} from "./Model";

export class JobBoardNavigator {
  constructor(
    private router: Router<ScreenName>,
    private isAuthenticated: boolean,
    private jobOffers: JobOfferRepository,
    private pricingPlan: PricingPlanPort,
  ) {
    this.addRoutes();
    this.router.addDefaultRoute('home');
  }

  private addRoutes(): void {
    this.router.addRoute('home');
    this.router.addRoute('show');
    this.router.addRoute('pricing');
    this.router.addRoute('form', () => {
      if (this.canCreateJob()) {
        return null;
      }
      return 'pricing';
    });
    this.router.addRoute('edit', params => {
      if (this.canEditJob(Number(params.id))) {
        return null;
      }
      return 'home';
    });
    this.router.addRoute('payment');
  }

  private canCreateJob(): boolean {
    return this.isAuthenticated && this.pricingPlan.pricingPlanSelected();
  }

  private canEditJob(jobOfferId: number): boolean {
    return this.isAuthenticated && this.jobOffers.canEdit(jobOfferId);
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

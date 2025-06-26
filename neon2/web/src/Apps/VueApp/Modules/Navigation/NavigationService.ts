import {Router} from "../../../../Packages/Core/Application/Router";
import {ScreenName} from "../JobBoard/Model";

export class NavigationService {
  constructor(private router: Router<ScreenName>) {}

  showJobOffers(): void {
    this.router.navigate('home', {});
  }

  showPricing(): void {
    this.router.navigate('pricing', {});
  }
}

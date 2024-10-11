import {Screens} from "../../../../../Screens";

export class NavigationService {
  constructor(private screens: Screens) {}

  showJobOffers(): void {
    this.screens.navigate('home', null);
  }

  showPricing(): void {
    this.screens.navigate('pricing', null);
  }
}

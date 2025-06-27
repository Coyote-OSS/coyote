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

  attemptRegister(): void {
    window.location.href = '/Register';
  }

  attemptLogin(): void {
    window.location.href = '/Login';
  }

  attemptLogout(): void {
    // implement logout
    // needs http post to /Logout
  }

  attemptHelp(): void {
    window.location.href = '/Pomoc';
  }

  attemptMessages() {
    window.location.href = '/User/Pm';
  }

  attemptAccount(): void {
    window.location.href = '/User';
  }

  attemptProfile(): void {
    // implement seeing profile
  }
}

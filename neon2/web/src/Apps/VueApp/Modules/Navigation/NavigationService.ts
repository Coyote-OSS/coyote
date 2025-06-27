import {Router} from "../../../../Packages/Core/Application/Router";
import {NavigationBackend} from "../../../../Packages/Core/Backend/NavigationBackend";
import {ScreenName} from "../JobBoard/Model";
import {useNavigationStore} from "./store";

export class NavigationService {
  constructor(
    private router: Router<ScreenName>,
    private backend: NavigationBackend,
    private csrfToken: string,
  ) {}

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
    const navigationStore = useNavigationStore();
    fetch('/Logout', {
      method: 'POST',
      headers,
      body: JSON.stringify({'_token': this.csrfToken}),
    })
      .then(() => {
        navigationStore.isAuthenticated = false;
        navigationStore.navigationUser = null;
      });
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
    window.location.href = this.backend.navigationUser()!.profileHref;
  }
}

const headers: HeadersInit = {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
};

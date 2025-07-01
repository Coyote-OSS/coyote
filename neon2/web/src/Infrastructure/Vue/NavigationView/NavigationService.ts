import {NavigationUser} from "../../../Domain/Navigation/NavigationUser";
import {ScreenName} from "../JobBoardView/Model";
import {Router} from "../Router";
import {useNavigationStore} from "./navigationStore";

export class NavigationService {
  constructor(
    private router: Router<ScreenName>,
    private csrfToken: string,
    private user: NavigationUser|null,
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

  attemptMessages(): void {
    window.location.href = '/User/Pm';
  }

  attemptAccount(): void {
    window.location.href = '/User';
  }

  attemptProfile(): void {
    window.location.href = this.user!.profileHref;
  }

  attemptNotifications(): void {
    window.location.href = '/User/Notifications';
  }
}

const headers: HeadersInit = {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
};

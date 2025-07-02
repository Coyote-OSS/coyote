import {ScreenName} from "../JobBoardView/Model";
import {Router} from "../Router";
import {NavigationView} from "./NavigationView";

export class NavigationService {
  constructor(
    private router: Router<ScreenName>,
    private csrfToken: string,
    private view: NavigationView,
  ) {}

  showJobOffers(): void {
    this.router.navigate('home', {});
  }

  showPricing(): void {
    this.router.navigate('pricing', {});
  }

  showForum(): void {
    window.location.href = '/Forum';
  }

  showBlog(): void {
    window.location.href = '/Mikroblogi';
  }

  attemptRegister(): void {
    window.location.href = '/Register';
  }

  attemptLogin(): void {
    window.location.href = '/Login';
  }

  attemptLogout(): void {
    fetch('/Logout', {
      method: 'POST',
      headers,
      body: JSON.stringify({'_token': this.csrfToken}),
    })
      .then(() => this.view.removeUser());
  }

  showRegister(): void {
    window.location.href = '/Register';
  }

  showLogin(): void {
    window.location.href = '/Login';
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
    window.location.href = this.view.userProfileHref();
  }

  attemptNotifications(): void {
    window.location.href = '/User/Notifications';
  }
}

const headers: HeadersInit = {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
};

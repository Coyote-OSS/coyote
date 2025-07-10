import {ScreenName} from "../JobBoardView/Model";
import {Router} from "../Router";
import {NavigationView} from "./NavigationView";

export type NavigationAction =
  'jobBoard'|'pricing'|'forum'|'blog'|
  'register'|'login'|'logout'|'help'|
  'account'|'profile'|'messages'|
  'toggleTheme'|'admin';

export class NavigationService {
  constructor(
    private router: Router<ScreenName>,
    private csrfToken: string,
    private view: NavigationView,
  ) {}

  action(action: NavigationAction): void {
    if (action === 'jobBoard') this.showJobBoard();
    if (action === 'pricing') this.showPricing();
    if (action === 'forum') this.showForum();
    if (action === 'blog') this.showBlog();
    if (action === 'register') this.showRegister();
    if (action === 'login') this.attemptLogin();
    if (action === 'logout') this.attemptLogout();
    if (action === 'help') this.attemptHelp();
    if (action === 'account') this.attemptAccount();
    if (action === 'profile') this.attemptProfile();
    if (action === 'messages') this.attemptMessages();
    if (action === 'toggleTheme') this.view.toggleTheme();
    if (action === 'admin') this.attemptAdministratorPanel();
  }

  showJobBoard(): void {
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

  attemptHelp(): void {
    window.location.href = '/Pomoc';
  }

  attemptMessages(): void {
    window.location.href = '/User/Pm';
  }

  attemptAdministratorPanel(): void {
    window.location.href = '/Adm';
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

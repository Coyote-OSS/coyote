import {CoyoteApi} from "../../Backend/CoyoteApi";
import {ScreenName} from "../JobBoardView/Model";
import {Router} from "../Router";
import {NavigationView} from "./NavigationView";
import {NavigationAction, NavigationService} from "./Port/NavigationService";
import {ThemeController} from "./ThemeController";

export class VueNavigationService implements NavigationService {
  private notificationsExhausted: boolean = false;

  constructor(
    private router: Router<ScreenName>,
    private csrfToken: string,
    private view: NavigationView,
    private themeController: ThemeController,
    private coyoteApi: CoyoteApi,
  ) {}

  action(action: NavigationAction): void {
    if (action === 'jobBoard') this.showJobBoard();
    if (action === 'pricing') this.showPricing();
    if (action === 'forum') this.showForum();
    if (action === 'blog') this.showBlog();
    if (action === 'register') this.attemptRegister();
    if (action === 'login') this.attemptLogin();
    if (action === 'logout') this.attemptLogout();
    if (action === 'help') this.attemptHelp();
    if (action === 'account') this.attemptAccount();
    if (action === 'profile') this.attemptProfile();
    if (action === 'messages') this.attemptMessages();
    if (action === 'toggleTheme') this.themeController.toggleTheme();
    if (action === 'admin') this.attemptAdministratorPanel();
    if (action === 'employerReviews') this.showEmployerReviews();
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

  loadMoreNotifications(): void {
    if (this.notificationsExhausted) return;
    const notifications: number = this.view.navigationUserNotificationsCount();
    this.coyoteApi
      .loadNotifications(notifications)
      .then(notifications => {
        if (notifications.length > 0) {
          this.view.addNotifications(notifications);
        } else {
          this.notificationsExhausted = true;
        }
      });
  }

  markAllNotificationsAsViewed(): void {
    this.coyoteApi.markAllNotificationsAsViewed()
      .then(() => this.view.markAllNotificationsAsViewed());
  }

  mainContentSuspended(suspended: boolean): void {
    this.view.mainContentSuspended(suspended);
  }

  showEmployerReviews(): void {
    window.location.href = 'https://4programmers.net/Forum/Opinie_o_pracodawcach';
  }
}

const headers: HeadersInit = {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
};

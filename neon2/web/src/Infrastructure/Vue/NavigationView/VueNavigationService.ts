import {SearchPrompt} from "../../../Application/Navigation/Port/SearchPrompt";
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
    private searchPrompt: SearchPrompt,
  ) {}

  action(action: NavigationAction): void {
    if (action === 'jobBoard') {
      this.router.navigate('home', {});
      return;
    }
    if (action === 'pricing') {
      this.router.navigate('pricing', {});
      return;
    }
    const historyModeHref = this.actionHref(action);
    if (historyModeHref) {
      window.location.href = historyModeHref;
    } else {
      if (action === 'logout') this.attemptLogout();
      if (action === 'toggleTheme') this.themeController.toggleTheme();
    }
  }

  actionHref(action: NavigationAction): string|null {
    if (action === 'jobBoard') return '/Job';
    if (action === 'pricing') return '/Job/pricing';
    if (action === 'forum') return '/Forum';
    if (action === 'blog') return '/Mikroblogi';
    if (action === 'employerReviews') return 'https://4programmers.net/Forum/Opinie_o_pracodawcach';
    if (action === 'register') return '/Register';
    if (action === 'login') return '/Login';
    if (action === 'help') return '/Pomoc';
    if (action === 'messages') return '/User/Pm';
    if (action === 'admin') return '/Adm';
    if (action === 'account') return '/User';
    if (action === 'profile') return this.view.userProfileHref();
    if (action === 'toggleTheme') return null;
    if (action === 'logout') return null;
    throw new Error('Failed to set action href.');
  }

  attemptLogout(): void {
    fetch('/Logout', {
      method: 'POST',
      headers,
      body: JSON.stringify({'_token': this.csrfToken}),
    })
      .then(() => this.view.removeUser());
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

  viewNotifications(): void {
    this.coyoteApi.viewAllNotifications()
      .then(() => this.view.viewNotifications());
  }

  markAllNotificationsAsViewed(): void {
    this.coyoteApi.markAllNotificationsAsViewed()
      .then(() => this.view.markAllNotificationsAsViewed());
  }

  mainContentSuspended(suspended: boolean): void {
    this.view.mainContentSuspended(suspended);
  }

  search(searchPhrase: string): void {
    this.view.setSearchItems([]);
    this.searchPrompt.prompt(searchPhrase)
      .then(items => this.view.setSearchItems(items));
  }
}

const headers: HeadersInit = {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
};

import {SearchPrompt} from "../../../neon2/web/src/Application/Navigation/Port/SearchPrompt";
import {CoyoteApi} from "../../../neon2/web/src/Infrastructure/Backend/CoyoteApi";
import {NavigationView} from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/NavigationView";
import {NavigationAction, NavigationService} from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/Port/NavigationService";
import {ThemeController} from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/ThemeController";

export class CoyoteNavigationService implements NavigationService {
  private notificationsExhausted: boolean = false;

  constructor(
    private csrfToken: string,
    private view: NavigationView,
    private themeController: ThemeController,
    private coyoteApi: CoyoteApi,
    private searchPrompt: SearchPrompt,
  ) {}

  action(action: NavigationAction): void {
    if (action === 'logout') this.attemptLogout();
    if (action === 'toggleTheme') this.toggleTheme();
    const historyModeHref = this.actionHref(action);
    if (historyModeHref) {
      window.location.href = historyModeHref;
      return;
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
    window.document['logout-form'].submit();
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

  private toggleTheme(): void {
    this.setDarkTheme(!this.view.isDarkTheme());
  }

  private setDarkTheme(dark: boolean): void {
    this.coyoteApi.toggleTheme(dark);
    this.view.setDarkTheme(dark);
    window.document.documentElement.classList.toggle('theme-dark', dark);
    window.document.documentElement.classList.toggle('theme-light', !dark);
    window.document.body.classList.toggle('theme-dark', dark);
    window.document.body.classList.toggle('theme-light', !dark);
    window.document.querySelector('body > header')?.classList?.toggle('dark', dark);
    window.document.querySelector('body > footer')?.classList?.toggle('dark', dark);
  }

  viewNotifications(): void {
    this.coyoteApi.markAllNotificationsAsViewed()
      .then(() => this.view.viewNotifications());
  }

  markAllNotificationsAsViewed(): void {
    this.coyoteApi.markAllNotificationsAsViewed()
      .then(() => this.view.markAllNotificationsAsViewed());
  }

  mainContentSuspended(suspended: boolean): void {
    this.htmlElementSuspend(window.document.querySelector('body > main') as HTMLElement, suspended);
    this.htmlElementSuspend(window.document.querySelector('body > footer') as HTMLElement, suspended);
  }

  private htmlElementSuspend(element: HTMLElement, suspended: boolean): void {
    if (suspended) {
      element.style.display = 'none';
    } else {
      element.style.display = 'block';
    }
  }

  search(searchPhrase: string): void {
    this.view.setSearchItems([]);
    this.searchPrompt.prompt(searchPhrase)
      .then(items => this.view.setSearchItems(items));
  }
}

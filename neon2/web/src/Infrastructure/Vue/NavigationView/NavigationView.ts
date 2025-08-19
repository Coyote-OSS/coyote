import {SearchItem} from "../../../Application/Navigation/Port/SearchPrompt";
import {NavigationUser} from "../../../Domain/Navigation/NavigationUser";
import {Notification} from "../../../Domain/Navigation/Notification";
import {NavigationForumMenu} from "./NavigationForumMenu";
import {NavigationStore} from "./View/navigationStore";

export class NavigationView {
  constructor(private readonly store: NavigationStore) {}

  setUser(navigationUser: NavigationUser|null): void {
    this.store.isAuthenticated = navigationUser !== null;
    this.store.navigationUser = navigationUser;
  }

  setDarkTheme(darkTheme: boolean): void {
    this.store.darkTheme = darkTheme;
    window.document.documentElement.classList.toggle('dark', darkTheme);
  }

  isDarkTheme(): boolean {
    return this.store.darkTheme;
  }

  removeUser(): void {
    this.store.isAuthenticated = false;
    this.store.navigationUser = null;
  }

  setNavigationForumMenu(navigationForumMenu: NavigationForumMenu): void {
    this.store.navigationForumMenu = navigationForumMenu;
  }

  userProfileHref(): string {
    if (this.store.navigationUser) {
      return this.navigationUser().profileHref;
    }
    throw new Error('Failed to read user profile href.');
  }

  navigationUserNotificationsCount(): number {
    return this.navigationUser().notifications.length;
  }

  addNotifications(notifications: Notification[]): void {
    this.navigationUser().notifications.push(...notifications);
  }

  viewNotifications(): void {
    this.navigationUser().notificationsCount = 0;
  }

  markAllNotificationsAsViewed(): void {
    this.navigationUser().notifications.forEach(notification => {
      notification.notificationHighlighted = false;
    });
    this.navigationUser().notificationsCount = 0;
  }

  private navigationUser(): NavigationUser {
    return this.store.navigationUser!;
  }

  mainContentSuspended(suspended: boolean): void {
    this.store.navigationMainContentSuspended = suspended;
  }

  setSearchItems(items: SearchItem[]): void {
    this.store.searchItems = items;
  }
}

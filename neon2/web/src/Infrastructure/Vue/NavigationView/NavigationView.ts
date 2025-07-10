import {NavigationUser} from "../../../Domain/Navigation/NavigationUser";
import {Notification} from "../../../Domain/Navigation/Notification";
import {NavigationForumMenu} from "./NavigationForumMenu";
import {NavigationStore, useNavigationStore} from "./View/navigationStore";

export class NavigationView {
  private readonly store: NavigationStore = useNavigationStore();

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
      return this.store.navigationUser!.profileHref;
    }
    throw new Error('Failed to read user profile href.');
  }

  navigationUserNotificationsCount(): number {
    return this.store.navigationUser!.notifications.length;
  }

  addNotifications(notifications: Notification[]): void {
    this.store.navigationUser!.notifications.push(...notifications);
  }
}

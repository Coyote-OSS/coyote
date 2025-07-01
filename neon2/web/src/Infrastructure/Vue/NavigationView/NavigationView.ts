import {NavigationUser} from "../../../Domain/Navigation/NavigationUser";
import {NavigationMenu} from "./NavigationMenu";
import {NavigationStore} from "./navigationStore";

export class NavigationView {
  constructor(private readonly store: NavigationStore) {}

  setAuthenticationState(loggedIn: boolean): void {
    this.store.$state.isAuthenticated = loggedIn;
  }

  setNavigationMenu(navigationMenu: NavigationMenu): void {
    this.store.$state.navigationMenu = navigationMenu;
  }

  setNavigationUser(navigationUser: NavigationUser|null): void {
    this.store.$state.navigationUser = navigationUser;
  }

  removeUser(): void {
    this.store.$state.isAuthenticated = false;
    this.store.$state.navigationUser = null;
  }

  userProfileHref(): string {
    if (this.store.$state.navigationUser) {
      return this.store.$state.navigationUser!.profileHref;
    }
    throw new Error('Failed to read user profile href.');
  }
}

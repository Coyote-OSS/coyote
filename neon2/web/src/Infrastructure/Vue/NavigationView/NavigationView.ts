import {NavigationUser} from "../../../Domain/Navigation/NavigationUser";
import {NavigationMenu} from "./NavigationMenu";
import {NavigationStore} from "./navigationStore";

export class NavigationView {
  constructor(private readonly store: NavigationStore) {}

  setAuthenticationState(loggedIn: boolean): void {
    this.store.isAuthenticated = loggedIn;
  }

  setNavigationMenu(navigationMenu: NavigationMenu): void {
    this.store.navigationMenu = navigationMenu;
  }

  setNavigationUser(navigationUser: NavigationUser|null): void {
    this.store.navigationUser = navigationUser;
  }
}

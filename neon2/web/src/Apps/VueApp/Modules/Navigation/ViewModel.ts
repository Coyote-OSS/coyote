import {BackendNavigationUser} from "../../../../Packages/Core/Backend/backendInput";
import {BackendNavigationMenu} from "../../../../Packages/Core/Backend/BackendNavigationMenu";
import {NavigationStore} from "./store";

export class ViewModel {
  constructor(private readonly store: NavigationStore) {}

  setAuthenticationState(loggedIn: boolean): void {
    this.store.isAuthenticated = loggedIn;
  }

  setNavigationMenu(navigationMenu: BackendNavigationMenu): void {
    this.store.navigationMenu = navigationMenu;
  }

  setNavigationUser(navigationUser: BackendNavigationUser|null): void {
    this.store.navigationUser = navigationUser;
  }
}

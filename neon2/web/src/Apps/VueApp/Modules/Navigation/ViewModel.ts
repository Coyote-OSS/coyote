import {BackendNavigationMenu} from "../../../../Packages/Core/Backend/BackendNavigationMenu";
import {NavigationStore} from "./store";

export class ViewModel {
  constructor(private readonly store: NavigationStore) {}

  setAuthenticationState(loggedIn: boolean): void {
    this.store.isAuthenticated = loggedIn;
  }

  setNavigationMenu(navigationMenu: BackendNavigationMenu): void {
    console.log(navigationMenu);
    this.store.navigationMenu = navigationMenu;
  }
}

import {NavigationUser} from "../../../Domain/Navigation/NavigationUser";
import {NavigationForumMenu} from "./NavigationForumMenu";
import {NavigationStore, useNavigationStore} from "./View/navigationStore";

export class NavigationView {
  private readonly store: NavigationStore = useNavigationStore();

  setAuthenticationState(loggedIn: boolean): void {
    this.store.$state.isAuthenticated = loggedIn;
  }

  setNavigationForumMenu(navigationForumMenu: NavigationForumMenu): void {
    this.store.$state.navigationForumMenu = navigationForumMenu;
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

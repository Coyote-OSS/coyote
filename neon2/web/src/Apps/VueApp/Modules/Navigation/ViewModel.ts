import {NavigationStore} from "./store";

export class ViewModel {
  constructor(private readonly store: NavigationStore) {}

  setAuthenticationState(loggedIn: boolean): void {
    this.store.isAuthenticated = loggedIn;
  }
}

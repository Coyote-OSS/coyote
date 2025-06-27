import {BackendInput, BackendNavigationUser} from "./backendInput";

export class NavigationBackend {
  private backendInput: BackendInput = window.backendInput;

  constructor() {}

  navigationUser(): BackendNavigationUser|null {
    return this.backendInput.navigationUser;
  }
}

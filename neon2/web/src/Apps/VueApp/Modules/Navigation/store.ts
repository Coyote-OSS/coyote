import {defineStore} from "pinia";
import {BackendNavigationUser} from "../../../../Packages/Core/Backend/backendInput";
import {BackendNavigationMenu} from "../../../../Packages/Core/Backend/BackendNavigationMenu";

export const useNavigationStore = defineStore('navigation', {
  state(): State {
    return {
      // authentication
      isAuthenticated: false,
      // navigation
      navigationMenu: null,
      navigationUser: null,
    };
  },
});

interface State {
  isAuthenticated: boolean;
  navigationMenu: BackendNavigationMenu|null;
  navigationUser: BackendNavigationUser|null;
}

export type NavigationStore = ReturnType<typeof useNavigationStore>;

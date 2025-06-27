import {defineStore} from "pinia";
import {BackendNavigationMenu} from "../../../../Packages/Core/Backend/BackendNavigationMenu";

export const useNavigationStore = defineStore('navigation', {
  state(): State {
    return {
      // authentication
      isAuthenticated: false,
      // navigation
      navigationMenu: null,
    };
  },
});

interface State {
  isAuthenticated: boolean;
  navigationMenu: BackendNavigationMenu|null;
}

export type NavigationStore = ReturnType<typeof useNavigationStore>;

import {defineStore} from "pinia";
import {NavigationUser} from "../../../Domain/Navigation/NavigationUser";
import {NavigationMenu} from "./NavigationMenu";

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
  navigationMenu: NavigationMenu|null;
  navigationUser: NavigationUser|null;
}

export type NavigationStore = ReturnType<typeof useNavigationStore>;

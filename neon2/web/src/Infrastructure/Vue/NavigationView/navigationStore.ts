import {defineStore} from "pinia";
import {NavigationUser} from "../../../Domain/Navigation/NavigationUser";
import {NavigationForumMenu} from "./NavigationForumMenu";

export const useNavigationStore = defineStore('navigation', {
  state(): State {
    return {
      // authentication
      isAuthenticated: false,
      // navigation
      navigationForumMenu: null,
      navigationUser: null,
    };
  },
});

interface State {
  isAuthenticated: boolean;
  navigationForumMenu: NavigationForumMenu|null;
  navigationUser: NavigationUser|null;
}

export type NavigationStore = ReturnType<typeof useNavigationStore>;

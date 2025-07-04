import {defineStore} from "pinia";
import {NavigationUser} from "../../../../Domain/Navigation/NavigationUser";
import {NavigationForumMenu} from "../NavigationForumMenu";

export const useNavigationStore = defineStore('navigation', {
  state(): State {
    return {
      // theme
      darkTheme: false,
      // authentication
      isAuthenticated: false,
      // navigation
      navigationForumMenu: null,
      navigationUser: null,
      navigationMainContentSuspended: false,
    };
  },
  actions: {
    mainContentSuspend(): void {
      this.$state.navigationMainContentSuspended = true;
    },
    mainContentRestore(): void {
      this.$state.navigationMainContentSuspended = false;
    },
  },
});

interface State {
  darkTheme: boolean;
  isAuthenticated: boolean;
  navigationForumMenu: NavigationForumMenu|null;
  navigationUser: NavigationUser|null;
  navigationMainContentSuspended: boolean;
}

export type NavigationStore = ReturnType<typeof useNavigationStore>;

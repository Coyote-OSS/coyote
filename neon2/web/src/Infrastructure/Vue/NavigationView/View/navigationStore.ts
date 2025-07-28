import {defineStore} from "pinia";
import {SearchItem} from "../../../../Application/Navigation/Port/SearchPrompt";
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
      // search
      searchItems: [],
      navigationEntryPointsSuspended: false,
    };
  },
});

interface State {
  darkTheme: boolean;
  isAuthenticated: boolean;
  navigationForumMenu: NavigationForumMenu|null;
  navigationUser: NavigationUser|null;
  navigationMainContentSuspended: boolean;
  searchItems: SearchItem[];
  navigationEntryPointsSuspended: boolean;
}

export type NavigationStore = ReturnType<typeof useNavigationStore>;

import {NavigationUser} from "../../../../../Domain/Navigation/NavigationUser";
import {IconName} from "../../../Icon/icons";
import {NavigationAction} from "../../NavigationService";

export interface AuthControlListItem {
  type: 'username'|'link'|'buttonPrimary'|'buttonSecondary'|'separatorDesktop'|'separatorMobile'|'spaceMobile';
  title?: string;
  icon?: IconName;
  action?: NavigationAction;
  messagesCount?: number;
}

export function authControlItems(user: NavigationUser|null): AuthControlListItem[] {
  if (user) {
    return [
      {type: 'separatorMobile'},
      {type: 'username', title: user.username, icon: 'navigationProfile', action: 'profile'},
      {type: 'separatorDesktop'},
      {type: 'link', title: 'Wiadomości', icon: 'navigationMessages', action: 'messages', messagesCount: user!.messagesCount},
      {type: 'link', title: 'Moje konto', icon: 'navigationAccount', action: 'account'},
      {type: 'link', title: 'Pomoc', icon: 'navigationHelp', action: 'help'},
      {type: 'separatorDesktop'},
      {type: 'spaceMobile'},
      {type: 'link', title: 'Wyloguj', icon: 'navigationNavigate', action: 'logout'},
    ];
  } else {
    return [
      {type: 'spaceMobile'},
      {type: 'buttonPrimary', title: 'Zarejestruj się', action: 'register'},
      {type: 'buttonSecondary', title: 'Zaloguj', icon: 'navigationNavigate', action: 'login'},
    ];
  }
}

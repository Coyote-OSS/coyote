import {NavigationUser} from "../../../../../Domain/Navigation/NavigationUser";
import {IconName} from "../../../Icon/icons";
import {NavigationAction} from "../../Port/NavigationService";

export interface AuthControlListItem {
  type: 'username'|'link'|'buttonPrimary'|'buttonSecondary'|'separatorDesktop'|'separatorMobile'|'spaceMobile';
  title?: string;
  icon?: IconName;
  action?: NavigationAction;
  messagesCount?: number;
}

export function authControlItems(
  user: NavigationUser|null,
  darkTheme: boolean,
): AuthControlListItem[] {
  const theme: AuthControlListItem = {
    type: 'link',
    title: darkTheme ? 'Jasny motyw' : 'Ciemny motyw',
    icon: darkTheme ? 'navigationThemeLight' : 'navigationThemeDark',
    action: 'toggleTheme',
  };
  if (user) {
    const administrator: AuthControlListItem[] =
      user.canAccessAdministratorPanel
        ? [{type: 'link', title: 'Panel administracyjny', icon: 'navigationAdmin', action: 'admin'}]
        : [];
    return [
      {type: 'separatorMobile'},
      {type: 'username', title: user.username, icon: 'navigationProfile', action: 'profile'},
      {type: 'separatorDesktop'},
      {type: 'link', title: 'Wiadomości', icon: 'navigationMessages', action: 'messages', messagesCount: user!.messagesCount},
      {type: 'link', title: 'Moje konto', icon: 'navigationAccount', action: 'account'},
      {type: 'link', title: 'Pomoc', icon: 'navigationHelp', action: 'help'},
      ...administrator,
      theme,
      {type: 'separatorDesktop'},
      {type: 'spaceMobile'},
      {type: 'link', title: 'Wyloguj', icon: 'navigationNavigate', action: 'logout'},
    ];
  } else {
    return [
      {type: 'separatorMobile'},
      theme,
      {type: 'separatorDesktop'},
      {type: 'spaceMobile'},
      {type: 'buttonPrimary', title: 'Zarejestruj się', action: 'register'},
      {type: 'buttonSecondary', title: 'Zaloguj', icon: 'navigationNavigate', action: 'login'},
    ];
  }
}

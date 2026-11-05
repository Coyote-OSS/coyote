import {NavigationAction} from "../../Port/NavigationService";

export const entryPointItems: EntryPointItem[] = [
  {
    type: 'link',
    title: 'Dla kandydatów',
    action: 'jobBoard',
    children: [
      {title: 'Oferty Pracy IT', action: 'jobBoard'},
      {title: 'Opinie o Pracodawcach', action: 'employerReviews'},
    ],
  },
  {
    type: 'link',
    title: 'Dla pracodawców',
    action: 'pricing',
    children: [
      {title: 'Cennik Ogłoszeń', action: 'pricing'},
      {title: 'Stwórz darmowy profil', action: 'register'},
    ],
    childrenForNotAuthenticated: true,
  },
  {type: 'link', title: 'Forum', action: 'forum', forumMenu: true},
  {type: 'link', title: 'Mikroblogi', action: 'blog'},
];

export interface EntryPointItem {
  type: 'link';
  title: string;
  action: NavigationAction;
  forumMenu?: boolean;
  children?: NavigationItem[];
  childrenForNotAuthenticated?: boolean;
}

export interface NavigationItem {
  title: string;
  action: NavigationAction;
}

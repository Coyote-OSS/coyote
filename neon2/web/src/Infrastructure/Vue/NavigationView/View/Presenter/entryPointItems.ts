import {NavigationAction} from "../../NavigationService";

export const entryPointItems: EntryPointItem[] = [
  {type: 'link', title: 'Dla kandydatów', action: 'jobBoard'},
  {type: 'link', title: 'Dla pracodawców', action: 'pricing'},
  {type: 'link', title: 'Forum', action: 'forum', forumMenu: true},
  {type: 'link', title: 'Mikroblogi', action: 'blog'},
];

export interface EntryPointItem {
  type: 'link';
  title: string;
  action: NavigationAction;
  forumMenu?: boolean;
}

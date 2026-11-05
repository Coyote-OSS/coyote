import {IconName} from "../Icon/icons";

export interface NavigationForumMenu {
  categories: Category[];
  topics: Topic[];
  allCategoriesHref: string;
  allTopicsHref: string;
  headerItems: HeaderItem[];
  userFilters: UserFilter[];
}

export interface Category {
  title: string;
  href: string;
  count: Count;
  isRead: boolean;
}

export interface Topic {
  title: string;
  subtitle: string;
  href: string;
  count: Count;
}

interface Count {
  long: string;
  short: string;
}

interface HeaderItem {
  title: string;
  icon: IconName;
  online?: boolean;
  help: string;
}

interface UserFilter {
  title: string;
  href: string;
  help?: string;
}

import {IconName} from "../Icon/icons";

export interface NavigationMenu {
  categories: NavigationCategory[];
  allCategoriesHref: string;
  headerItems: NavigationHeaderItem[];
  footerItems: NavigationFooterItem[];
}

interface NavigationCategory {
  title: string;
  icon: IconName;
  href: string;
  items: NavigationCategoryItem[];
}

interface NavigationCategoryItem {
  title: string;
  subtitle: string;
  promoted: boolean;
  trending: boolean;
  href: string;
  count: {
    long: string;
    short: string;
  };
}

interface NavigationHeaderItem {
  title: string;
  icon: IconName;
  online?: boolean;
  help: string;
}

interface NavigationFooterItem {
  title: string;
  href: string;
  help?: string;
}

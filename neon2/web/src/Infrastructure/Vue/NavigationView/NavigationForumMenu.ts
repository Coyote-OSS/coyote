import {IconName} from "../Icon/icons";

export interface NavigationForumMenu {
  categories: Category[];
  allCategoriesHref: string;
  headerItems: HeaderItem[];
  footerItems: FooterItem[];
}

interface Category {
  title: string;
  icon: IconName;
  href?: string;
  items: CategoryItem[];
}

interface CategoryItem {
  title: string;
  subtitle: string|null;
  promoted?: boolean;
  trending?: boolean;
  href: string;
  count: {
    long: string;
    short: string;
  };
}

interface HeaderItem {
  title: string;
  icon: IconName;
  online?: boolean;
  help: string;
}

interface FooterItem {
  title: string;
  href: string;
  help?: string;
}

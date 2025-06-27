import {IconName} from "../../../Apps/VueApp/Icon/icons";

export interface BackendNavigationMenu {
  categories: BackendNavigationCategory[];
  allCategoriesHref: string;
  headerItems: BackendNavigationHeaderItem[];
  footerItems: BackendNavigationFooterItem[];
}

interface BackendNavigationCategory {
  title: string;
  icon: IconName;
  href: string;
  items: BackendNavigationCategoryItem[];
}

interface BackendNavigationCategoryItem {
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

interface BackendNavigationHeaderItem {
  title: string;
  icon: IconName;
  online?: boolean;
  help: string;
}

interface BackendNavigationFooterItem {
  title: string;
  href: string;
  help?: string;
}

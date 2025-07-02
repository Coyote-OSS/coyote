import {IconName} from "../Icon/icons";

export interface NavigationForumMenu {
  sections: Section[];
  allCategoriesHref: string;
  headerItems: HeaderItem[];
  footerItems: FooterItem[];
}

interface Section {
  title: string;
  icon: IconName;
  href?: string;
  items: SectionItem[];
}

interface SectionItem {
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

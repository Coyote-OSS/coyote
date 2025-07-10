export interface FooterMenu {
  contactUsMail: string;
  contactUsFacebookHref: string;
  contactUsLinkedInHref: string;
  contactUsGithubHref: string;
  sections: FooterMenuSection[];
  copyrightYear: string;
  executionTime: string;
}

interface FooterMenuSection {
  title: string;
  items: FooterMenuSectionItem[];
}

interface FooterMenuSectionItem {
  title: string;
  href: string;
}

import {PlanBundle} from "../../Domain/JobBoard/PlanBundle";
import {NavigationUser} from "../../Domain/Navigation/NavigationUser";
import {FooterMenu} from "../Vue/NavigationView/FooterMenu";
import {NavigationForumMenu} from "../Vue/NavigationView/NavigationForumMenu";

import {BackendJobOffer} from "./BackendJobOffer";

export interface BackendInput {
  jobOffers: BackendJobOffer[];
  testMode: boolean;
  planBundle: PlanBundle;
  userId: number|null;
  navigationUser: NavigationUser|null;
  jobOfferApplicationEmail: string|null;
  csrfToken: string;
  stripePublishableKey: string|null;
  paymentInvoiceCountries: Array<{countryCode: string; countryName: string}>;
  darkTheme: boolean;
  themeMode: 'dark'|'light'|'system';
  acceptanceTagNames: string[];
  navigationForumMenu: NavigationForumMenu;
  footerMenu: FooterMenu;
  websocketUrl: string|null;
  websocketSubscribeCommand: string|null;
}

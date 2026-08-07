import {Tag} from "./Tag";

export interface ForumJobOfferTile {
  companyName: string;
  companyLogoUrl: string|null;
  jobOfferHref: string;
  offerTitle: string;
  headerPills: string[];
  salary: string;
  technologyTags: Tag[];
}

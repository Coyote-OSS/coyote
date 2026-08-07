import {Tag} from "./Tag";

export interface ForumJobOfferTile {
  companyName: string;
  companyLogoUrl: string|null;
  jobOfferHref: string;
  jobOfferTitle: string;
  headerPills: string[];
  salary: string;
  technologyTags: Tag[];
}

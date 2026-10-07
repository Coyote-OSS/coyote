import {Tag} from "./Tag";

export interface ForumJobOfferTile {
  companyName: string;
  companyLogoUrl: string|null;
  jobOfferHref: string;
  jobOfferClickHref: string;
  jobOfferExposureHref: string;
  jobOfferTitle: string;
  headerPills: string[];
  salaryFormat: string;
  salaryDisclosed: boolean;
  isNew: boolean;
  technologyTags: Tag[];
}

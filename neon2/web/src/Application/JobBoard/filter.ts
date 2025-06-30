import {LegalForm, WorkExperience, WorkMode} from "../../Domain/JobBoard/JobBoard";
import {OrderBy} from "./orderBy";

export interface Filter {
  searchPhrase: string;
  tags: string[];
  locations: string[];
  workModes: WorkMode[];
  legalForms: LegalForm[];
  workExperiences: WorkExperience[];
  sort: OrderBy;
}

export interface FilterOptions {
  tags: string[];
  locations: string[];
}

import {Filter} from "../../../../../../Packages/Feature/JobBoard/Application/filter";

export function emptyJobOfferFilter(): Filter {
  return {
    legalForms: [],
    locations: [],
    searchPhrase: '',
    sort: 'promoted',
    tags: [],
    workExperiences: [],
    workModes: [],
  };
}

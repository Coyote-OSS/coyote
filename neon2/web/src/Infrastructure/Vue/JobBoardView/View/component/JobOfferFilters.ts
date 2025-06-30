import {Filter} from "../../../../../Application/JobBoard/filter";

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

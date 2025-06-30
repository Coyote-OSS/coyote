import {JobOffer} from "../../Domain/JobBoard/JobOffer";

export type OrderBy = 'promoted'|'most-recent'|'highest-salary'|'lowest-salary';

export function sortInPlace(offers: JobOffer[], orderBy: OrderBy): void {
  if (orderBy === 'promoted') {
    return;
  }
  if (orderBy === 'most-recent') {
    offers.sort((offer1: JobOffer, offer2: JobOffer): number => offer1.id > offer2.id ? -1 : 1);
    return;
  }
  offers.sort((offer1: JobOffer, offer2: JobOffer): number => {
    const s1 = offer1.salaryRangeTo;
    const s2 = offer2.salaryRangeTo;
    if (s1 === null && s2 === null) return 0;
    if (s1 === null) return 1;
    if (s2 === null) return -1;
    if (orderBy === 'highest-salary') {
      return s2 - s1;
    }
    if (orderBy === 'lowest-salary') {
      return s1 - s2;
    }
    return 0;
  });
}

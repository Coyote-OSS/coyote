export interface JobOffer {
  title: string;
  url: string;
  publishDate: string;
  salaryTo: number|null;
  salaryFrom: number|null;
  salaryCurrency: Currency|null;
  salaryIsNet: boolean|null;
  salaryRate: Rate|null;
  workMode: WorkMode;
  locations: string[];
  companyName: string|null;
  companyLogoUrl: string|null;
  tagNames: string[];
  legalForm: LegalForm;
  isFavourite: boolean;
  isMine: boolean;
}

export type WorkMode = 'stationary'|'hybrid'|'fullyRemote';
export type LegalForm = 'employment'|'b2b'|'of-mandate'|'specific-task';
export type Currency = 'PLN'|'EUR'|'USD'|'GBP'|'CHF';
export type Rate = 'hourly'|'monthly'|'weekly'|'yearly';
export type OrderBy = 'most-recent'|'highest-salary'|'lowest-salary';

type JobOffersListener = (jobOffers: JobOffer[]) => void;

type Predicate<T> = (argument: T) => boolean;
type Function<I, O> = (argument: I) => O;

export class Filters {
  private jobOffers: JobOffer[] = [];
  private updateListener: JobOffersListener|null = null;
  private orderBy: OrderBy = 'most-recent';
  private searchPhrase: string = '';
  private minimumSalary: number = 0;
  private workModes: WorkMode[] = [];
  private locations: string[] = [];
  private tags: string[] = [];
  private onlyFavourite: boolean;
  private onlyMine: boolean;
  private legalForms: LegalForm[] = [];

  clearFilters(): void {
    this.searchPhrase = '';
    this.locations = [];
    this.tags = [];
    this.minimumSalary = 0;
    this.workModes = [];
    this.orderBy = 'most-recent';
    this.update();
  }

  onUpdate(listener: JobOffersListener): void {
    this.updateListener = listener;
  }

  addJobOffer(jobOffer: JobOffer): void {
    this.jobOffers.push(jobOffer);
    this.update();
  }

  filter(searchPhrase: string): void {
    this.searchPhrase = searchPhrase;
    this.update();
  }

  filterBySalary(minimumSalary: number): void {
    this.minimumSalary = minimumSalary;
    this.update();
  }

  filterByWorkMode(workModes: WorkMode[]): void {
    this.workModes = workModes;
    this.update();
  }

  filterByLocation(locations: string[]): void {
    this.locations = locations;
    this.update();
  }

  filterByTags(tags: string[]): void {
    this.tags = tags;
    this.update();
  }

  filterByFavourite(onlyFavourite: boolean): void {
    this.onlyFavourite = onlyFavourite;
    this.update();
  }

  filterByMine(onlyMine: boolean): void {
    this.onlyMine = onlyMine;
    this.update();
  }

  filterByLegalForm(legalForms: LegalForm[]): void {
    this.legalForms = legalForms;
    this.update();
  }

  sortByPublishDate(): void {
    this.sort('most-recent');
  }

  sortByHighestSalary(): void {
    this.sort('highest-salary');
  }

  sort(order: OrderBy): void {
    this.orderBy = order;
    this.update();
  }

  availableLocations(): string[] {
    return this.flatMap(offer => offer.locations);
  }

  availableTags(): string[] {
    return this.flatMap(offer => offer.tagNames);
  }

  private flatMap(simplifier: Function<JobOffer, string[]>): string[] {
    return [...new Set(this.jobOffers.flatMap(simplifier))];
  }

  availableSalaries(): number[] {
    return [5000, 10000, 15000, 20000, 25000, 30000];
  }

  private update(): void {
    if (this.updateListener) {
      this.updateListener(this.filteredJobOffersInOrder());
    }
  }

  private filteredJobOffersInOrder(): JobOffer[] {
    const offers = this.jobOffers
      .filter(offer => offer.title.toLowerCase().includes(this.searchPhrase.toLowerCase()))
      .filter(offer => offer.salaryTo >= this.minimumSalary)
      .filter(offer => this.matchesByLocation(offer))
      .filter(offer => this.matchesByTag(offer))
      .filter(offer => this.matchesByWorkMode(offer))
      .filter(offer => this.matchesByFavourite(offer))
      .filter(offer => this.matchesByMine(offer))
      .filter(offer => this.matchesByLegalForm(offer));
    this.sortInPlace(offers);
    return offers;
  }

  private matchesByLocation(offer: JobOffer): boolean {
    return this.matchesByPredicate(this.locations, location => offer.locations.includes(location));
  }

  private matchesByTag(offer: JobOffer): boolean {
    return this.matchesByPredicate(this.tags, tag => offer.tagNames.includes(tag));
  }

  private matchesByPredicate(options: string[], predicate: Predicate<string>): boolean {
    if (options.length === 0) {
      return true;
    }
    return options.filter(predicate).length > 0;
  }

  private matchesByWorkMode(offer: JobOffer): boolean {
    if (this.workModes.length === 0) {
      return true;
    }
    return this.workModes.includes(offer.workMode);
  }

  private matchesByLegalForm(offer: JobOffer): boolean {
    if (this.legalForms.length === 0) {
      return true;
    }
    return this.legalForms.includes(offer.legalForm);
  }

  private matchesByFavourite(offer: JobOffer): boolean {
    if (this.onlyFavourite) {
      return offer.isFavourite;
    }
    return true;
  }

  private matchesByMine(offer: JobOffer): boolean {
    if (this.onlyMine) {
      return offer.isMine;
    }
    return true;
  }

  private sortInPlace(offers: JobOffer[]): void {
    if (this.orderBy === 'most-recent') {
      offers.sort((offer1: JobOffer, offer2: JobOffer): number => {
        return offer1.publishDate > offer2.publishDate ? -1 : 1;
      });
    }
    if (this.orderBy === 'highest-salary') {
      offers.sort((offer1: JobOffer, offer2: JobOffer): number => {
        return offer1.salaryTo > offer2.salaryTo ? -1 : 1;
      });
    }
    if (this.orderBy === 'lowest-salary') {
      offers.sort((offer1: JobOffer, offer2: JobOffer): number => {
        return offer1.salaryTo < offer2.salaryTo ? -1 : 1;
      });
    }
  }

  count(): number {
    let count = 0;
    if (this.searchPhrase.length > 0) {
      count++;
    }
    if (this.locations.length > 0) {
      count++;
    }
    if (this.workModes.length > 0) {
      count++;
    }
    if (this.tags.length > 0) {
      count++;
    }
    if (this.minimumSalary > 0) {
      count++;
    }
    return count;
  }
}

export interface JobOffer {
  title: string;
  url: string;
  publishDate: string;
  salaryTo: number|null;
  salaryFrom: number|null;
  workMode: WorkMode;
  locations: string[];
  companyName: string|null;
}

export type WorkMode = 'remote'|'stationary';

type JobOffersListener = (jobOffers: JobOffer[]) => void;
export type OrderBy = 'most-recent'|'highest-salary';

export class Filters {
  private jobOffers: JobOffer[] = [];
  private updateListener: JobOffersListener|null = null;
  private orderBy: OrderBy = 'most-recent';
  private _searchPhrase: string = '';
  private minimumSalary: number = 0;
  private workModeRemote: boolean = false;
  private locations: string[] = [];

  clearFilters(): void {
    this._searchPhrase = '';
    this.locations = [];
    this.minimumSalary = 0;
    this.workModeRemote = false;
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
    this._searchPhrase = searchPhrase;
    this.update();
  }

  filterBySalary(minimumSalary: number): void {
    this.minimumSalary = minimumSalary;
    this.update();
  }

  filterByWorkModeRemote(workModeRemote: boolean): void {
    this.workModeRemote = workModeRemote;
    this.update();
  }

  filterByLocation(locations: string[]): void {
    this.locations = locations;
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
    return [...new Set(this.jobOffers.flatMap(offer => offer.locations))];
  }

  availableSalaries(): number[] {
    return [5000, 10000, 15000, 20000, 25000, 30000];
  }

  searchPhrase(): string {
    return this._searchPhrase;
  }

  private update(): void {
    if (this.updateListener) {
      this.updateListener(this.filteredJobOffersInOrder());
    }
  }

  private filteredJobOffersInOrder(): JobOffer[] {
    const offers = this.jobOffers
      .filter(offer => offer.title.toLowerCase().includes(this._searchPhrase.toLowerCase()))
      .filter(offer => offer.salaryTo >= this.minimumSalary)
      .filter(offer => {
        if (this.workModeRemote) {
          return offer.workMode === 'remote';
        }
        return true;
      })
      .filter(offer => {
        if (this.locations.length === 0) {
          return true;
        }
        for (const location of this.locations) {
          if (offer.locations.includes(location)) {
            return true;
          }
        }
        return false;
      })
    ;
    this.sortInPlace(offers);
    return offers;
  }

  private sortInPlace(offers: JobOffer[]): void {
    if (this.orderBy === 'most-recent') {
      offers.sort((offer1: JobOffer, offer2: JobOffer): number => {
        return offer1.publishDate > offer2.publishDate ? -1 : 1;
      });
    } else {
      offers.sort((offer1: JobOffer, offer2: JobOffer): number => {
        return offer1.salaryTo > offer2.salaryTo ? -1 : 1;
      });
    }
  }
}

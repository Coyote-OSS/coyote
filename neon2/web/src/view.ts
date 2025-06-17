import {JobOfferFilterService} from "./JobOfferFilterService";
import {Filter} from "./neon3/Packages/Feature/JobBoard/Application/filter";
import {JobOfferRepository} from "./neon3/Packages/Feature/JobBoard/Application/JobOfferRepository";
import {VueUiFactory} from './ui';

export class View {
  private filter: Filter|null = null;
  public filterOnlyMine: boolean = false;

  constructor(
    private ui: VueUiFactory,
    private jobOffers: JobOfferRepository,
  ) {}

  filterJobOffers(): void {
    if (this.filterOnlyMine) {
      this.ui.setJobOffers(this.jobOffers.onlyMine());
    } else {
      const f = new JobOfferFilterService(this.jobOffers, this.filter!);
      this.ui.setJobOffers(f.filterJobOffers());
    }
  }

  notifyFilterChanged(filter: Filter): void {
    this.filter = filter;
  }
}

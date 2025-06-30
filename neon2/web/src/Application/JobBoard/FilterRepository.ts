import {Filter} from "./filter";

export class FilterRepository {
  public filter: Filter|null = null;
  public filterOnlyMine: boolean = false;

  setFilter(filter: Filter): void {
    this.filter = filter;
  }

  setFilterOnlyMine(onlyMine: boolean): void {
    this.filterOnlyMine = onlyMine;
  }
}

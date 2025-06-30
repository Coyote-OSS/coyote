export interface PlanBundle {
  hasBundle: boolean;
  remainingJobOffers: number|null;
  planBundleName: 'strategic'|'growth'|'scale'|null;
}

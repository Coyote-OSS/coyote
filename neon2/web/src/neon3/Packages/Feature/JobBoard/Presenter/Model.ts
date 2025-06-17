export interface PaymentSummary {
  basePrice: number;
  vat: number;
  bundleSize: 1|3|5|20;
  vatIncluded: boolean;
}

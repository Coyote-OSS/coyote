export function isVatIncluded(countryCode: string, vatId: string): boolean {
  if (['PL', 'UA', 'US', 'JP', 'SG'].includes(countryCode)) {
    return true;
  }
  return vatId === '';
}

export function fewLocations(cities_: string[]): string[] {
  if (cities_.length > 3) {
    return [...cities_.slice(0, 2), '+' + (cities_.length - 2).toString()];
  }
  return cities_;
}

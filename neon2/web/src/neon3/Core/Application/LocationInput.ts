export interface LocationInput {
  mount(input: HTMLInputElement, listener: LocationListener): void;
}

export interface LocationListener {
  mounted(): void;
  select(location: Location): void;
  abort(): void;
}

export interface Location {
  latitude: number;
  longitude: number;
  city: string|null;
  streetName: string|null;
  streetNumber: string|null;
  countryCode: string|null;
  postalCode: string|null;
}

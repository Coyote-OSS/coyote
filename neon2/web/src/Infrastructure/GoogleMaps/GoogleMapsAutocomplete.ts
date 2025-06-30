import {googleMapsLoaded} from "./googleMaps";
import {Location, LocationInput, LocationListener} from "../../Application/JobBoard/Port/LocationInput";
import PlaceResult = google.maps.places.PlaceResult;

export class GoogleMapsAutocomplete implements LocationInput {
  mount(input: HTMLInputElement, listener: LocationListener): void {
    googleMapsLoaded(() => this.mountAutocomplete(input, listener));
  }

  private mountAutocomplete(input: HTMLInputElement, listener: LocationListener): void {
    const autocomplete = new google.maps.places.Autocomplete(input);
    autocomplete.addListener('place_changed', (): void => {
      this.callListener(listener, autocomplete.getPlace());
    });
    input.addEventListener('blur', () => listener.abort());
    listener.mounted();
  }

  private callListener(listener: LocationListener, place: PlaceResult): void {
    if (place.address_components) {
      listener.select(this.parseLocation(place));
    } else {
      listener.abort();
    }
  }

  private parseLocation(place: PlaceResult): Location {
    return {
      latitude: place.geometry!.location!.lat(),
      longitude: place.geometry!.location!.lng(),
      city: this.addressComponent(place, 'postal_town')
        || this.addressComponent(place, 'locality')
        || this.addressComponent(place, 'neighborhood'),
      streetName: this.addressComponent(place, 'route'),
      streetNumber: this.addressComponent(place, 'street_number'),
      countryCode: this.addressComponentShort(place, 'country'),
      postalCode: this.addressComponent(place, 'postal_code'),
    };
  }

  private addressComponent(place: PlaceResult, type: string): string|null {
    for (const item of place.address_components!) {
      if (item.types[0] === type) {
        return item.long_name;
      }
    }
    return null;
  }

  private addressComponentShort(place: PlaceResult, type: string): string|null {
    for (const item of place.address_components!) {
      if (item.types[0] === type) {
        return item.short_name;
      }
    }
    return null;
  }
}

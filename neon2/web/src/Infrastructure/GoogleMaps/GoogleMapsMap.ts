import {googleMapsLoaded} from "./googleMaps";
import {LocationDisplay} from "../../Application/JobBoard/Port/LocationDisplay";

export class GoogleMapsMap implements LocationDisplay {
  mount(element: HTMLElement, latitude: number, longitude: number): void {
    googleMapsLoaded(() => this.mountAddress(element, latitude, longitude));
  }

  mountAddress(element: HTMLElement, latitude: number, longitude: number): void {
    const location = {lat: latitude, lng: longitude};
    const map = new google.maps.Map(element, {
      zoom: 12,
      center: location,
    });
    new google.maps.Marker({position: location, map});
  }
}

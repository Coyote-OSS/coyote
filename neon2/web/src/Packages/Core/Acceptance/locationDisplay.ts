import {LocationDisplay} from "../Application/LocationDisplay";
import {GoogleMapsMap} from "../External/GoogleMaps/GoogleMapsMap";
import {TestLocationDisplay} from "./TestLocationDisplay";

export function locationDisplay(testMode: boolean): LocationDisplay {
  return testMode
    ? new TestLocationDisplay()
    : new GoogleMapsMap();
}

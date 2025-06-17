import {LocationInput} from "../Application/LocationInput";
import {GoogleMapsAutocomplete} from "../External/GoogleMaps/GoogleMapsAutocomplete";
import {TestLocationInput} from "./TestLocationInput";

export function locationInput(testMode: boolean): LocationInput {
  return testMode
    ? new TestLocationInput()
    : new GoogleMapsAutocomplete();
}

import {LocationDisplay} from "../JobBoard/Port/LocationDisplay";
import {LocationInput} from "../JobBoard/Port/LocationInput";
import {PaymentProvider} from "../JobBoard/Port/PaymentProvider";
import {TagAutocomplete} from "../JobBoard/Port/TagAutocomplete";

export interface ApplicationMode {
  locationDisplay(): LocationDisplay;
  locationInput(): LocationInput;
  paymentProvider(): PaymentProvider;
  tagAutocomplete(): TagAutocomplete;
}

import {CoyoteTagAutocomplete} from "../../Application/JobBoard/CoyoteTagAutocomplete";
import {ApplicationInbound} from "../../Application/JobBoard/Port/ApplicationInbound";
import {LocationDisplay} from "../../Application/JobBoard/Port/LocationDisplay";
import {LocationInput} from "../../Application/JobBoard/Port/LocationInput";
import {PaymentProvider} from "../../Application/JobBoard/Port/PaymentProvider";
import {TagAutocomplete} from "../../Application/JobBoard/Port/TagAutocomplete";
import {TestTagAutocomplete} from "../../Application/JobBoard/TestTagAutocomplete";
import {CoyoteSearchPrompt} from "../../Application/Navigation/CoyoteSearchPrompt";
import {SearchPrompt} from "../../Application/Navigation/Port/SearchPrompt";
import {TestSearchPrompt} from "../../Application/Navigation/TestSearchPrompt";
import {ApplicationMode} from "../../Application/Port/ApplicationMode";
import {GoogleMapsAutocomplete} from "../GoogleMaps/GoogleMapsAutocomplete";
import {GoogleMapsMap} from "../GoogleMaps/GoogleMapsMap";
import {StripePaymentProvider} from "../PaymentProvider/StripePaymentProvider";
import {TestLocationDisplay} from "./TestLocationDisplay";
import {TestLocationInput} from "./TestLocationInput";
import {TestPaymentProvider} from "./TestPaymentProvider";

export function applicationMode(inbound: ApplicationInbound): ApplicationMode {
  if (inbound.testMode()) {
    return new AcceptanceTestMode(
      inbound.acceptanceTagNames(),
      inbound.acceptanceSearchItems());
  }
  return new ProductionMode(inbound.stripeKey()!);
}

class AcceptanceTestMode implements ApplicationMode {
  constructor(
    private acceptanceTagNames: string[],
    private acceptanceSearchItems: string[],
  ) {}

  locationDisplay(): LocationDisplay {
    return new TestLocationDisplay();
  }

  locationInput(): LocationInput {
    return new TestLocationInput();
  }

  paymentProvider(): PaymentProvider {
    return new TestPaymentProvider();
  }

  tagAutocomplete(): TagAutocomplete {
    return new TestTagAutocomplete(this.acceptanceTagNames);
  }

  searchPrompt(): SearchPrompt {
    return new TestSearchPrompt(this.acceptanceSearchItems);
  }
}

class ProductionMode implements ApplicationMode {
  constructor(private stripeKey: string) {}

  locationDisplay(): LocationDisplay {
    return new GoogleMapsMap();
  }

  locationInput(): LocationInput {
    return new GoogleMapsAutocomplete();
  }

  paymentProvider(): PaymentProvider {
    return new StripePaymentProvider(this.stripeKey);
  }

  tagAutocomplete(): TagAutocomplete {
    return new CoyoteTagAutocomplete();
  }

  searchPrompt(): SearchPrompt {
    return new CoyoteSearchPrompt();
  }
}

import {assertEquals, describe, test} from "../../../../../../test/assertion";
import {fewLocations} from "./JobOfferListItem";

describe('Given a job offer has many locations, only a few of them are presented.', () => {
  test('No locations are presented as empty locations', () => {
    assertEquals([], fewLocations([]));
  });
  describe('Only the first 3 locations are presented.', () => {
    test('One location is presented as-is.', () => {
      assertEquals(['London'], fewLocations(['London']));
    });
    test('Two locations are presented as-is.', () => {
      assertEquals(
        ['London', 'NY'],
        fewLocations(['London', 'NY']));
    });
    test('Three locations are presented as-is.', () => {
      assertEquals(
        ['London', 'NY', 'Hel'],
        fewLocations(['London', 'NY', 'Hel']));
    });
    test('Given four locations, the last two are hidden.', () => {
      assertEquals(
        ['London', 'NY', '+2'],
        fewLocations([
          'London',
          'NY',
          'Madrid',
          'Oslo',
        ]));
    });
    test('Given five locations, the last three are hidden.', () => {
      assertEquals(
        ['London', 'NY', '+3'],
        fewLocations([
          'London',
          'NY',
          'Madrid',
          'Oslo',
          'Warsaw',
        ]));
    });
  });
});

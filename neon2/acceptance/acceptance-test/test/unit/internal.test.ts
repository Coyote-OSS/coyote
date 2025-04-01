import {test} from '@playwright/test';
import {Mangler} from '../../internal/mangler';
import {assertEquals, assertNotEquals} from "../../internal/playwright";

test.describe('Acceptance test internals', () => {
  test.describe('Name mangling', () => {
    let mangler: Mangler;
    test.beforeEach(() => {
      mangler = new Mangler();
    });
    test('Name is encoded to a different string.', () => {
      assertNotEquals('Mark', mangler.encoded('Mark'));
    });
    test('Encoding the same name twice returns the same hash', () => {
      assertEquals(mangler.encoded('Foo'), mangler.encoded('Foo'));
    });
    test('Name can be decoded to the same string.', () => {
      assertEquals('Mark', mangler.decoded(mangler.encoded('Mark')));
    });
    test('Previously mangled name after reset is returned as the hash itself.', () => {
      mangler.reset();
      assertEquals('123', mangler.decoded('123'));
    });
    test('An array of encoded names can be decoded.', () => {
      const [foo, bar] = mangler.decodedAll([
        mangler.encoded('Foo'),
        mangler.encoded('Bar'),
      ]);
      assertEquals('Foo', foo);
      assertEquals('Bar', bar);
    });
    test('The same name is encoded to a different hash after a reset.', () => {
      const before = mangler.encoded('Foo');
      mangler.reset();
      const after = mangler.encoded('Foo');
      assertNotEquals(before, after);
    });
  });
});

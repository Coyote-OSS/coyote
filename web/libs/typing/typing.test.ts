import {describe, test} from 'vitest';
import {assertEquals, assertThrows} from '../../test/assertion';
import {defined} from './typing';

describe('defined', () => {
  test('a defined value is returned', () => {
    assertEquals('value', defined('value'));
  });

  test('a falsy value is returned', () => {
    assertEquals(0, defined(0));
    assertEquals('', defined(''));
    assertEquals(false, defined(false));
  });

  test('null is returned', () => {
    assertEquals(null, defined(null));
  });

  test('an undefined value throws', () => {
    assertThrows('Failed to access an undefined value.',
      () => defined(undefined));
  });
});

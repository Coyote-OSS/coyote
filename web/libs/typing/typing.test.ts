import {describe, test} from 'vitest';
import {assertEquals, assertThrows} from '../../test/assertion';
import {defined, nonNull} from './typing';

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

describe('nonNull', () => {
  test('a non-null value is returned', () => {
    assertEquals('value', nonNull('value'));
  });

  test('a falsy value is returned', () => {
    assertEquals(0, nonNull(0));
    assertEquals('', nonNull(''));
    assertEquals(false, nonNull(false));
  });

  test('undefined is returned', () => {
    assertEquals(undefined, nonNull(undefined));
  });

  test('a null value throws', () => {
    assertThrows('Failed to access a null value.',
      () => nonNull(null));
  });
});

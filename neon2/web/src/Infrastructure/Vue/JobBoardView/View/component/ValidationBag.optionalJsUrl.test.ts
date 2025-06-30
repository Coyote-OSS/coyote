import {assertEquals, assertFalse, assertTrue, describe, test} from "../../../../../../test/assertion";
import {prependJsUrlProtocol, ValidationBag} from "./ValidationBag";

describe('Validation rules.', () => {
  describe('Js url rule.', () => {
    describe('Protocol is prepended if missing.', () => {
      test('An url without protocol is valid and is prepended an https protocol.',
        () => assertFieldValue(
          'https://4programmers.net',
          '4programmers.net'));

      describe('An valid url with a protocol is not changed.', () => {
        test('Url with https keeps https.', () => assertFieldValue(
          'https://4programmers.net',
          'https://4programmers.net'));
        test('Url with http keeps http.', () => assertFieldValue(
          'http://4programmers.net',
          'http://4programmers.net'));
      });
    });

    test('Malformed js url is not valid.',
      () => assertFieldInvalid('http://?'));

    test('A youtube video is a valid url',
      () => assertFieldValid('https://www.youtube.com/watch?v=abc123'));

    test('Url with a space in the middle is not valid.',
      () => assertFieldInvalid('http://foo bar/'));

    test('Url with surrounding spaces is valid',
      () => assertFieldInvalid('  http://foo  '));
  });
});

function assertFieldValue(expected: any, fieldValue: string): void {
  assertFieldValid(fieldValue);
  assertEquals(expected, fieldOptionalValue(fieldValue),
    'Failed to assert that a field value is represented as expected.');
}

function assertFieldValid(fieldValue: string): void {
  assertTrue(fieldOptionalValid(fieldValue),
    'Failed to assert that a field value is valid.');
}

function assertFieldInvalid(fieldValue: string): void {
  assertFalse(fieldOptionalValid(fieldValue),
    'Failed to assert that a field value is invalid.');
}

function fieldOptionalValid(fieldValue: string): boolean {
  const validation = new ValidationBag({fieldValue}, ['fieldValue']);
  const [success] = validation.validate(rules => {
    rules.optionalJsUrl('fieldValue', 'Error message.');
  });
  return success;
}

function fieldOptionalValue(fieldValue: string): any {
  return prependJsUrlProtocol(fieldValue);
}

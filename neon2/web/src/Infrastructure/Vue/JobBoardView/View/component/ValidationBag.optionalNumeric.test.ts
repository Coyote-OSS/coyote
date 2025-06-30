import {assertEquals, assertFalse, assertTrue, describe, test} from "../../../../../../test/assertion";
import {parseNumber} from "./JobOfferForm";
import {ValidationBag} from "./ValidationBag";

describe('Validation rules.', () => {
  describe('Numeric field rule.', () => {
    test('String containing only digits is valid and presented as a number.',
      () => assertFieldValue(123, '123'));

    test('Empty field is valid and represented as null.',
      () => assertFieldValue(null, ''));

    describe('Surrounding spaces are ignored.', () => {
      test('Digits with surrounding spaces are valid and represented as a number.',
        () => assertFieldValue(123, '  123  '));
    });

    test('A word is not a valid numeric string.',
      () => assertFieldInvalid('abc'));

    test('Non-digit characters are invalid.',
      () => assertFieldInvalid('123 abc'));

    test('Formatting spaces are valid and ignored.',
      () => assertFieldValue(40000, '40 0 00'));

    test('Denoted negative integer is not valid.',
      () => assertFieldInvalid('-10'));

    test('Denoted positive integer is not valid.',
      () => assertFieldInvalid('+10'));

    test('Leading zeros are valid.',
      () => assertFieldValue(0, '000'));

    test('Formatting spaces are valid and ignored.',
      () => assertFieldValue(40000, '40,0,00'));

    test('Period in a numeric value is not valid.',
      () => assertFieldInvalid('40.000'));
  });
});

function assertFieldValue(expected: any, fieldValue: string): void {
  assertTrue(fieldOptionalNumericValid(fieldValue),
    'Failed to assert that a field value is valid.');
  assertEquals(expected, fieldOptionalNumericValue(fieldValue),
    'Failed to assert that a field value is represented as expected.');
}

function assertFieldInvalid(fieldValue: string): void {
  assertFalse(fieldOptionalNumericValid(fieldValue),
    'Failed to assert that a field value is invalid.');
}

function fieldOptionalNumericValid(fieldValue: string): boolean {
  const validation = new ValidationBag({fieldValue}, ['fieldValue']);
  const [success] = validation.validate(rules => {
    rules.optionalNumeric('fieldValue', 'Error message.');
  });
  return success;
}

function fieldOptionalNumericValue(fieldValue: string): any {
  return parseNumber(fieldValue);
}

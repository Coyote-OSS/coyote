import {assertFalse, assertTrue, describe, test} from "../../../../../../test/assertion";
import {ValidationBag} from "./ValidationBag";

describe('Validation rules.', () => {
  const hostnames = ['www.youtube.com', 'youtube.com', 'youtu.be'];

  describe('Js url rule.', () => {
    test('Not provided value is valid.',
      () => assertFieldValid('', hostnames));

    test('Url without protocol is valid.',
      () => assertFieldValid('youtube.com', hostnames));

    test('Malformed js url is not valid.',
      () => assertFieldInvalid('http://?', []));

    test('A youtube video is a valid url.',
      () => assertFieldValid('https://www.youtube.com/watch?v=abc123', hostnames));

    test('A youtube video without www is a valid url.',
      () => assertFieldValid('https://youtube.com/watch?v=abc123', hostnames));

    test('A short youtube video is a valid url.',
      () => assertFieldValid('https://youtu.be/abc123', hostnames));

    test('Video not from youtube is not valid.',
      () => assertFieldInvalid('https://4programmers.net', hostnames));
  });
});

function assertFieldValid(fieldValue: string, hostnames: string[]): void {
  assertTrue(fieldOptionalValid(fieldValue, hostnames),
    'Failed to assert that a field value is valid.');
}

function assertFieldInvalid(fieldValue: string, hostnames: string[]): void {
  assertFalse(fieldOptionalValid(fieldValue, hostnames),
    'Failed to assert that a field value is invalid.');
}

function fieldOptionalValid(fieldValue: string, hostnames: string[]): boolean {
  const validation = new ValidationBag({fieldValue}, ['fieldValue']);
  const [success] = validation.validate(rules => {
    rules.optionalJsUrlHostname('fieldValue', hostnames, 'Error message.');
  });
  return success;
}

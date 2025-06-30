import {assertEquals, assertThrows, describe, test} from "../../../../../../test/assertion";
import {simplifiedUrl} from "./JobOfferField";

describe('Job offer show.', () => {
  describe('Simplified url', () => {
    test('Url with a protocol is presented without the protocol.', () => {
      assertEquals('4programmers.net', simplifiedUrl('http://4programmers.net'));
    });
    test('Url with leading www is presented without www.', () => {
      assertEquals('4programmers.net', simplifiedUrl('http://www.4programmers.net'));
    });
    test('Url with infix www is returned as is.', () => {
      assertEquals('subdomain.www.net', simplifiedUrl('http://subdomain.www.net'));
    });
    test('Url with a path is presented without it.', () => {
      assertEquals('4programmers.net', simplifiedUrl('http://4programmers.net?path'));
    });
    test('Url without a protocol is not valid.', () => {
      assertThrows(() => {
        simplifiedUrl('4programmers.net');
      }, 'Failed to simplify an invalid url.');
    });
  });
});

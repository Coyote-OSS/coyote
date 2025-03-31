import {expect} from "vitest";

export {test, describe} from "vitest";

export function assertEquals(expected: any, actual: any): void {
  expect(actual).to.equal(expected);
}

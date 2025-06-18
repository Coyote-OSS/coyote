import {expect} from "vitest";

export {test, beforeEach, describe} from "vitest";

export function assertTrue(actual: any, errorMessage?: string): void {
  expect(actual).to.deep.equal(true, errorMessage);
}

export function assertFalse(actual: any, errorMessage?: string): void {
  expect(actual).to.deep.equal(false, errorMessage);
}

export function assertEquals(expected: any, actual: any, errorMessage?: string): void {
  expect(actual).to.deep.equal(expected, errorMessage);
}

export function assertThrows(block: Runnable, expectedMessage: string): void {
  expect(block).to.throw(expectedMessage);
}

type Runnable = () => void;

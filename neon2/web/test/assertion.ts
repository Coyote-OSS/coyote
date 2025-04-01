import {expect} from "vitest";

export {test, beforeEach, describe} from "vitest";

export function assertEquals(expected: any, actual: any): void {
  expect(actual).to.deep.equal(expected);
}

export function assertNotEquals(expected: any, actual: any): void {
  expect(actual).to.not.deep.equal(expected);
}

export function assertContains(expected: any, actual: any): void {
  expect(actual).to.contain(expected);
}

export function assertNotContains(expected: any, actual: any): void {
  expect(actual).to.not.contain(expected);
}

export function assertThrows(block: Runnable, expectedMessage: string): void {
  expect(block).to.throw(expectedMessage);
}

type Runnable = () => void;

export class Spy<T> {
  private lastArgument: T|null = null;

  capture(): (argument: T) => void {
    return (argument: T): void => {
      this.lastArgument = argument;
    };
  }

  assertCalledWith(argument: T): void {
    assertEquals(argument, this.lastArgument);
  }
}

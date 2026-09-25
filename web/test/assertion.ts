import {expect} from 'vitest';

export function assertEquals(expected: unknown, actual: unknown): void {
  expect(actual).toStrictEqual(expected);
}

type Runnable = () => void;

export function assertThrows(expectedMessage: string, block: Runnable): void {
  expect(block).toThrow(expectedMessage);
}

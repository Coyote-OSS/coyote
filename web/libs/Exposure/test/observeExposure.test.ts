import {afterEach, beforeEach, describe, test, vi} from 'vitest';
import {assertEquals} from '../../../test/assertion';
import {observeExposure} from '../src/observeExposure';
import {FakeIntersectionObserver} from "./FakeIntersectionObserver";

describe('observeExposure', () => {
  const element = {} as Element;
  let exposures: number;

  beforeEach(() => {
    vi.useFakeTimers();
    vi.stubGlobal('IntersectionObserver', FakeIntersectionObserver);
    exposures = 0;
    observeExposure(element, () => exposures++);
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.unstubAllGlobals();
  });

  test('an element that is never visible is not exposed', () => {
    vi.advanceTimersByTime(5000);
    assertEquals(0, exposures);
  });

  test('an element visible long enough is exposed', () => {
    FakeIntersectionObserver.instance.showFraction(0.5);
    vi.advanceTimersByTime(1000);
    assertEquals(1, exposures);
  });

  test('an element visible too briefly is not exposed', () => {
    FakeIntersectionObserver.instance.showFraction(0.5);
    vi.advanceTimersByTime(999);
    assertEquals(0, exposures);
  });

  test('an element hidden before the duration elapsed is not exposed', () => {
    FakeIntersectionObserver.instance.showFraction(0.5);
    vi.advanceTimersByTime(500);
    FakeIntersectionObserver.instance.showFraction(0);
    vi.advanceTimersByTime(5000);
    assertEquals(0, exposures);
  });

  test('an element visible below the threshold is not exposed', () => {
    FakeIntersectionObserver.instance.showFraction(0.49);
    vi.advanceTimersByTime(5000);
    assertEquals(0, exposures);
  });

  test('an element shown again is exposed only once', () => {
    FakeIntersectionObserver.instance.showFraction(0.5);
    vi.advanceTimersByTime(1000);
    FakeIntersectionObserver.instance.showFraction(0);
    FakeIntersectionObserver.instance.showFraction(0.5);
    vi.advanceTimersByTime(1000);
    assertEquals(1, exposures);
  });

  test('an element partially visible above the threshold is exposed', () => {
    FakeIntersectionObserver.instance.showFraction(0.75);
    vi.advanceTimersByTime(1000);
    assertEquals(1, exposures);
  });

  test('an element shown again after being hidden too early is exposed', () => {
    FakeIntersectionObserver.instance.showFraction(0.5);
    vi.advanceTimersByTime(500);
    FakeIntersectionObserver.instance.showFraction(0);
    FakeIntersectionObserver.instance.showFraction(0.5);
    vi.advanceTimersByTime(1000);
    assertEquals(1, exposures);
  });

  test('an exposed element is no longer observed', () => {
    FakeIntersectionObserver.instance.showFraction(0.5);
    vi.advanceTimersByTime(1000);
    assertEquals([], FakeIntersectionObserver.instance.observed);
  });
});

describe('observeExposure without IntersectionObserver', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  test('observing is skipped, in a browser without IntersectionObserver', () => {
    vi.stubGlobal('IntersectionObserver', undefined);
    let exposures = 0;
    observeExposure({} as Element, () => exposures++);
    assertEquals(0, exposures);
  });
});

export class FakeIntersectionObserver {
  static instance: FakeIntersectionObserver;
  readonly observed: Element[] = [];

  constructor(private readonly callback: IntersectionObserverCallback) {
    FakeIntersectionObserver.instance = this;
  }

  observe(element: Element): void {
    this.observed.push(element);
  }

  unobserve(element: Element): void {
    this.observed.splice(this.observed.indexOf(element), 1);
  }

  disconnect(): void {
    this.observed.length = 0;
  }

  showFraction(intersectionRatio: number): void {
    const entry = {isIntersecting: intersectionRatio > 0, intersectionRatio} as IntersectionObserverEntry;
    this.callback([entry], this as unknown as IntersectionObserver);
  }
}

export function observeExposure(element: Element, onExposure: () => void): void {
  if (typeof IntersectionObserver !== 'undefined') {
    new ExposureObserver(
      element,
      0.5, // fraction of the element that must be visible
      1000, // how long it must stay visible to count as exposure
      onExposure,
    ).observe();
  }
}

class ExposureObserver {
  private readonly observer: IntersectionObserver;
  private timer: ReturnType<typeof setTimeout>|undefined;
  private reported = false;

  constructor(
    private readonly element: Element,
    private readonly threshold: number,
    private readonly durationMs: number,
    private readonly onExposure: () => void,
  ) {
    this.observer = new IntersectionObserver(
      entries => this.handleIntersection(entries),
      {threshold: this.threshold},
    );
  }

  observe(): void {
    this.observer.observe(this.element);
  }

  private handleIntersection(entries: IntersectionObserverEntry[]): void {
    entries.forEach((entry) => {
      if (entry.isIntersecting && entry.intersectionRatio >= this.threshold) {
        this.startTimer();
      } else {
        this.clearTimer();
      }
    });
  }

  private startTimer(): void {
    if (this.timer !== undefined) {
      return;
    }
    this.timer = setTimeout(() => {
      this.timer = undefined;
      this.reportExposure();
      this.observer.unobserve(this.element);
    }, this.durationMs);
  }

  private clearTimer(): void {
    if (this.timer !== undefined) {
      clearTimeout(this.timer);
      this.timer = undefined;
    }
  }

  private reportExposure(): void {
    if (this.reported) {
      return;
    }
    this.reported = true;
    this.onExposure();
  }
}

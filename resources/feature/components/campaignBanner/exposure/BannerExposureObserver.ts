export class BannerExposureObserver {
  private readonly observer: IntersectionObserver;
  private timer: number|undefined;
  private reported = false;

  constructor(
    private readonly element: HTMLImageElement,
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
    this.timer = window.setTimeout(() => {
      this.timer = undefined;
      this.reportExposure();
      this.observer.unobserve(this.element);
    }, this.durationMs);
  }

  private clearTimer(): void {
    if (this.timer !== undefined) {
      window.clearTimeout(this.timer);
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

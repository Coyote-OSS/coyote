interface JobOfferView {
  (title: string): void;
}

export class JobBoard {
  constructor(private view: JobOfferView) {
  }

  addJobOffer(title: string): void {
    this.view(title);
  }
}

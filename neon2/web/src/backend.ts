export class JobBoardBackend {
  private autoInc: number = 1;

  addJobOffer(title: string, plan: 'free'|'paid', created: (id: number, expiresInDays: number) => void): void {
    simulateNetwork(() => {
      created(this.autoInc++, plan === 'free' ? 14 : 30);
    });
  }

  updateJobOffer(id: number, title: string, updated: () => void): void {
    simulateNetwork(() => {
      updated();
    });
  }

  initialJobOffers(): BackendJobOffer[] {
    return [];
  }
}

function simulateNetwork(block: Runnable): void {
  setTimeout(block, 125);
}

type Runnable = () => void;

interface BackendJobOffer {
  id: number;
  title: string;
  expiresInDays: number;
}

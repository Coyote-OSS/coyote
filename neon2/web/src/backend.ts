export class JobBoardBackend {
  private autoInc: number;

  constructor(private storage: BackendStorage) {
    this.autoInc = this.initialAutoInc();
  }

  private initialAutoInc(): number {
    const savedOffers = this.storedOffers();
    if (savedOffers.length > 0) {
      return Math.max(...savedOffers.map(o => o.id)) + 1;
    }
    return 1;
  }

  addJobOffer(title: string, plan: 'free'|'paid', created: (id: number, expiresInDays: number) => void): void {
    simulateNetwork(() => {
      const jobOffer = {
        id: this.autoInc++,
        title,
        expiresInDays: plan === 'free' ? 14 : 30,
      };
      this.storeOffers([...this.storedOffers(), jobOffer]);
      created(jobOffer.id, jobOffer.expiresInDays);
    });
  }

  updateJobOffer(id: number, title: string, updated: () => void): void {
    simulateNetwork(() => {
      const offers = this.storedOffers();
      const index = offers.findIndex(o => o.id === id);
      if (index !== -1) {
        offers[index].title = title;
        this.storeOffers(offers);
      }
      updated();
    });
  }

  initialJobOffers(): BackendJobOffer[] {
    return this.storedOffers();
  }

  private storedOffers(): BackendJobOffer[] {
    const data = this.storage.read();
    return data ? JSON.parse(data) : [];
  }

  private storeOffers(offers: BackendJobOffer[]): void {
    this.storage.write(JSON.stringify(offers));
  }
}

export class LocalStorage implements BackendStorage {
  write(value: string): void {
    localStorage.setItem('jobOffers', value);
  }

  read(): string|null {
    return localStorage.getItem('jobOffers') || null;
  }
}

export interface BackendStorage {
  write(value: string): void;
  read(): string|null;
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

import {JobOffer} from '../src/jobBoard';
import {NavigationListener, Screen, SearchListener, UserInterface, ViewListener} from '../src/view/ui/ui';
import {Toast, View} from '../src/view/view';
import {assertEquals, beforeEach, describe, test} from './assertion';

describe('JobBoard View', () => {
  let view: View;
  let ui: MemoryUi;
  beforeEach(() => {
    ui = new MemoryUi();
    view = new View(ui);
  });
  describe('Job offer state state is announced with a toast.', () => {
    test('Job offer creation is announced with a toast.', () => {
      view.jobOfferCreated(1, 'free');
      assertEquals('created', ui.lastToast());
    });
    test('Job offer edit is announced with a toast.', async () => {
      view.jobOfferEdited();
      assertEquals('edited', ui.lastToast());
    });
  });

  describe('The toast disappears after navigating to other screen', () => {
    test('Given a toast visible in screen, when interface is navigated to other screen, the toast is not visible.', () => {
      view.jobOfferCreated(1, 'free');
      ui.navigate();
      assertEquals(null, ui.lastToast());
    });
  });

  describe('Job offers can be searched by title using a search phrase.', () => {
    test('Given a job offer, when search phrase is contained in the job offer title, the job offer is listed.', () => {
      view.setJobOffers([jobOffer('Big Blue Cheeseburger')]);
      ui.search('Blue');
      const [offer] = ui.lastJobOffers();
      assertEquals('Big Blue Cheeseburger', offer.title);
    });

    test('Given a job offer, when search phrase is not contained in the job offer title, the job offer is not listed.', () => {
      view.setJobOffers([jobOffer('Big Blue Cheeseburger')]);
      ui.search('Red');
      assertEquals([], ui.lastJobOffers());
    });

    test('Given a search phrase, when a new job offer is added that does not contain the search phrase, the job offer is not listed.', () => {
      ui.search('Green');
      view.setJobOffers([jobOffer('Big Blue Cheeseburger')]);
      assertEquals([], ui.lastJobOffers());
    });

    describe('Search is case-insensitive', () => {
      test('Given a lowercase job offer title, when search phrase is uppercase, the job offer is listed.', () => {
        view.setJobOffers([jobOffer('Worker')]);
        ui.search('WORKER');
        const [offer] = ui.lastJobOffers();
        assertEquals('Worker', offer.title);
      });
    });

    function jobOffer(title: string): JobOffer {
      return {title, expiresInDays: 14, id: 1, status: 'published'};
    }
  });

  describe('Changing state of a job navigates to appropriate screen.', () => {
    test('Creating a free job, changes screen to home.', () => {
      ui.setScreen('pricing');
      view.jobOfferCreated(1, 'free');
      assertEquals('home', ui.screen());
    });
    test('Creating a paid job, changes screen to payment.', () => {
      ui.setScreen('pricing');
      view.jobOfferCreated(1, 'paid');
      assertEquals('payment', ui.screen());
    });
    test('Updating a job offer navigates back to home.', () => {
      ui.setScreen('show');
      view.jobOfferEdited();
      assertEquals('home', ui.screen());
    });
    test('Paying for a job offer navigates to home.', () => {
      ui.setScreen('payment');
      view.jobOfferPaid();
      assertEquals('home', ui.screen());
    });
  });
});

class MemoryUi implements UserInterface {
  private naviListeners: NavigationListener[] = [];
  private searchListeners: SearchListener[] = [];
  private toast: Toast|null = null;
  private _jobOffers: JobOffer[] = [];
  private _screen: Screen = 'home';

  mount(cssSelector: string): void {
  }

  setJobOffers(jobOffers: JobOffer[]): void {
    this._jobOffers = jobOffers;
  }

  setToast(toast: Toast|null): void {
    this.toast = toast;
  }

  addNavigationListener(listener: NavigationListener): void {
    this.naviListeners.push(listener);
  }

  addSearchListener(listener: SearchListener): void {
    this.searchListeners.push(listener);
  }

  addViewListener(listener: ViewListener): void {
  }

  setScreen(screen: Screen): void {
    this._screen = screen;
  }

  setCurrentJobOfferId(jobOfferId: number): void {
  }

  navigate(): void {
    this.naviListeners.forEach(listener => listener());
  }

  search(searchPhrase: string): void {
    this.searchListeners.forEach(listener => listener(searchPhrase));
  }

  lastToast(): Toast|null {
    return this.toast;
  }

  lastJobOffers(): JobOffer[] {
    return this._jobOffers;
  }

  screen(): Screen {
    return this._screen;
  }
}

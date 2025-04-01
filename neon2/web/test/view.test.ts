import {JobOffer} from '../src/jobBoard';
import {NavigationListener, UserInterface, ViewListener} from '../src/view/ui/ui';
import {Toast, View} from '../src/view/view';
import {assertEquals, beforeEach, describe, test} from './assertion';

describe('JobBoard View', () => {
  let ui: MemoryUi;
  beforeEach(() => {
    ui = new MemoryUi();
  });
  describe('Job offer state state is announced with a toast.', () => {
    test('Job offer creation is announced with a toast.', () => {
      new View(ui).toastCreated();
      assertEquals('created', ui.lastToast());
    });
    test('Job offer edit is announced with a toast.', async () => {
      new View(ui).toastEdited();
      assertEquals('edited', ui.lastToast());
    });
  });

  describe('The toast disappears after navigating to other screen', () => {
    test('Given a toast visible in screen, when interface is navigated to other screen, the toast is not visible.', () => {
      new View(ui).toastCreated();
      ui.navigate();
      assertEquals(null, ui.lastToast());
    });
  });
});

class MemoryUi implements UserInterface {
  private listeners: NavigationListener[] = [];
  private toast: Toast|null = null;

  mount(cssSelector: string): void {
  }

  setJobOffers(jobOffers: JobOffer[]): void {
  }

  setToast(toast: Toast|null): void {
    this.toast = toast;
  }

  addNavigationListener(listener: NavigationListener): void {
    this.listeners.push(listener);
  }

  addViewListener(listener: ViewListener): void {
  }

  navigate(): void {
    this.listeners.forEach(listener => listener());
  }

  lastToast(): Toast|null {
    return this.toast;
  }
}

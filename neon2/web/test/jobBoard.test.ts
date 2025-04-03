import {JobBoard, JobOffer} from "../src/jobBoard";
import {assertContains, assertEquals, assertNotContains, assertThrows, beforeEach, describe, test} from "./assertion";

describe('Job board', () => {
  let board: JobBoard;
  let lastViewSnapshot: JobOffer[]|null = null;

  beforeEach(() => {
    board = new JobBoard((viewUpdate: JobOffer[]) => lastViewSnapshot = viewUpdate);
    lastViewSnapshot = null;
  });

  function publishedOffers(): JobOffer[] {
    board.updateView();
    return lastViewSnapshot!;
  }

  function jobOffer(title: string): JobOffer {
    return {id: 1, title, expiresInDays: 14, status: 'published'};
  }

  function publishedOfferTitles(): string[] {
    return publishedOffers().map(offer => offer.title);
  }

  function jobOfferCreated(title: string): void {
    board.jobOfferCreated(jobOffer(title));
  }

  function addJobOfferWaitingPayment(jobOfferId: number, title: string): void {
    board.jobOfferCreated({id: jobOfferId, title, expiresInDays: 14, status: 'awaitingPayment'});
  }

  describe('Job board updates the view with job offers.', () => {
    test('When a new job offer is added, the job board updates the view.', () => {
      let wasCalled = false;
      const board = new JobBoard(() => wasCalled = true);
      board.jobOfferCreated(jobOffer('Foo'));
      assertEquals(true, wasCalled);
    });
    test('When a new job offer is added, the job board passes the job offer title to the view.', () => {
      jobOfferCreated('Blue offer');
      assertContains('Blue offer', publishedOfferTitles());
    });
    test("When a second job offer is added, the job board passes both job offers' titles to the view.", () => {
      jobOfferCreated('Red offer');
      jobOfferCreated('Blue offer');
      assertContains('Red offer', publishedOfferTitles());
      assertContains('Blue offer', publishedOfferTitles());
    });
    describe('Modifying an update model does not influence internal state', () => {
      test('Initially, job board has 0 job offers.', () => {
        assertEquals(0, board.count());
      });
      test('Adding job offers increases count.', () => {
        jobOfferCreated('Blue');
        assertEquals(1, board.count());
      });
      test('Clearing an update model list does not decrease count.', () => {
        let view;
        const board = new JobBoard((v) => view = v);
        board.jobOfferCreated(jobOffer('Foo'));
        view.length = 0;
        assertEquals(1, board.count());
      });
      test('Modifying an update model object does not pollute internal state.', () => {
        jobOfferCreated('Original name');
        const [offerSnapshot] = publishedOffers();
        offerSnapshot.title = 'Modified';
        board.updateView();
        assertEquals(['Original name'], publishedOfferTitles());
      });
    });
  });
  describe('Job offer title can be updated.', () => {
    test('Given a job offer, when its title is updated, the title is no longer passed to view.', () => {
      jobOfferCreated('Before');
      const [offer] = publishedOffers();
      board.jobOfferUpdated(offer.id, 'After');
      assertNotContains('Before', publishedOfferTitles());
    });
    test('Given a job offer, when its title is updated, the new title is passed to view.', () => {
      jobOfferCreated('Before');
      const [offer] = publishedOffers();
      board.jobOfferUpdated(offer.id, 'After');
      assertContains('After', publishedOfferTitles());
    });
    test('When a non-existing job offer is edited, an exception is thrown.', () => {
      const nonExistingId = 123;
      assertThrows(
        () => board.jobOfferUpdated(nonExistingId, ''),
        'No such job offer.');
    });
    test('Updating an offer preserves its id.', () => {
      jobOfferCreated('Before');
      const [offer] = publishedOffers();
      board.jobOfferUpdated(offer.id, 'First edit');
      const [afterEdit] = publishedOffers();
      assertEquals(offer.id, afterEdit.id);
    });
  });
  test('Paid job offer is not listable.', () => {
    addJobOfferWaitingPayment(1, 'To be paid');
    assertEquals([], publishedOffers());
  });
  test('Given an unpaid job offer, when it is paid, it is listable.', () => {
    addJobOfferWaitingPayment(42, 'Paid job offer');
    board.jobOfferPaid(42);
    const [jobOffer] = publishedOffers();
    assertEquals('Paid job offer', jobOffer.title);
  });
});

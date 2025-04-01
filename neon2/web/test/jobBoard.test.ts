import {JobBoard, JobOffer, Toast} from "../src/jobBoard";
import {
  assertContains,
  assertEquals,
  assertNotContains,
  assertNotEquals,
  assertThrows,
  beforeEach,
  describe,
  Spy,
  test,
} from "./assertion";

describe('Job board', () => {
  let board: JobBoard;
  let lastViewSnapshot: JobOffer[]|null = null;

  beforeEach(() => {
    board = new JobBoard((viewUpdate: JobOffer[]) => lastViewSnapshot = viewUpdate);
    lastViewSnapshot = null;
  });

  function lastViewSnapshotTitles(): string[] {
    return lastViewSnapshot!.map(offer => offer.title);
  }

  function addJobOffer(title: string): void {
    board.jobOfferCreated({id: 1, title, expiresInDays: 14});
  }

  describe('Job board updates the view with job offers.', () => {
    test('When a new job offer is added, the job board updates the view.', () => {
      addJobOffer('Blue offer');
      assertNotEquals(null, lastViewSnapshot);
    });
    test('When a new job offer is added, the job board passes the job offer title to the view.', () => {
      addJobOffer('Blue offer');
      assertContains('Blue offer', lastViewSnapshotTitles());
    });
    test("When a second job offer is added, the job board passes both job offers' titles to the view.", () => {
      addJobOffer('Red offer');
      addJobOffer('Blue offer');
      assertContains('Red offer', lastViewSnapshotTitles());
      assertContains('Blue offer', lastViewSnapshotTitles());
    });
    describe('Modifying an update model does not influence internal state', () => {
      test('Initially, job board has 0 job offers.', () => {
        assertEquals(0, board.count());
      });
      test('Adding job offers increases count.', () => {
        addJobOffer('Blue');
        assertEquals(1, board.count());
      });
      test('Clearing an update model list does not decrease count.', () => {
        addJobOffer('Blue offer');
        lastViewSnapshot!.length = 0;
        assertEquals(1, board.count());
      });
      test('Modifying an update model object does not pollute internal state.', () => {
        addJobOffer('Original name');
        const [offerSnapshot] = lastViewSnapshot!;
        offerSnapshot.title = 'Modified';
        board.updateView();
        assertEquals(['Original name'], lastViewSnapshotTitles());
      });
    });
  });
  describe('Job offer title can be updated.', () => {
    test('Given a job offer, when its title is updated, the title is no longer passed to view.', () => {
      addJobOffer('Before');
      const [offer] = lastViewSnapshot!;
      board.jobOfferUpdated(offer.id, 'After');
      assertNotContains('Before', lastViewSnapshotTitles());
    });
    test('Given a job offer, when its title is updated, the new title is passed to view.', () => {
      addJobOffer('Before');
      const [offer] = lastViewSnapshot!;
      board.jobOfferUpdated(offer.id, 'After');
      assertContains('After', lastViewSnapshotTitles());
    });
    test('When a non-existing job offer is edited, an exception is thrown.', () => {
      const nonExistingId = 123;
      assertThrows(
        () => board.jobOfferUpdated(nonExistingId, ''),
        'No such job offer.');
    });
    test('Updating an offer preserves its id.', () => {
      addJobOffer('Before');
      const [offer] = lastViewSnapshot!;
      board.jobOfferUpdated(offer.id, 'First edit');
      const [afterEdit] = lastViewSnapshot!;
      assertEquals(offer.id, afterEdit.id);
    });
  });
  describe('Job offer state state is announced with a toast.', () => {
    test('Job offer creation is announced with a toast.', () => {
      const spy = new Spy<Toast>();
      board.onToast(spy.capture());
      board.jobOfferCreated({title: '', id: 0, expiresInDays: 0});
      spy.assertCalledWith('created');
    });
    test('Job offer edit is announced with a toast.', async () => {
      const spy = new Spy<Toast>();
      board.jobOfferCreated({id: 1, title: '', expiresInDays: 0});
      board.onToast(spy.capture());
      board.jobOfferUpdated(1, '');
      spy.assertCalledWith('edited');
    });
  });
});

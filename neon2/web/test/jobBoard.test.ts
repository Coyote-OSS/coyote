import {JobBoard, JobOffer} from "../src/jobBoard";
import {
  assertContains,
  assertEquals,
  assertNotContains,
  assertNotEquals,
  assertThrows,
  beforeEach,
  describe,
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
    board.addJobOffer(title, 'free');
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
      board.updateJobOffer(offer.id, 'After');
      assertNotContains('Before', lastViewSnapshotTitles());
    });
    test('Given a job offer, when its title is updated, the new title is passed to view.', () => {
      addJobOffer('Before');
      const [offer] = lastViewSnapshot!;
      board.updateJobOffer(offer.id, 'After');
      assertContains('After', lastViewSnapshotTitles());
    });
    test('When a non-existing job offer is edited, an exception is thrown.', () => {
      const nonExistingId = 123;
      assertThrows(
        () => board.updateJobOffer(nonExistingId, ''),
        'No such job offer.');
    });
    test('Adding two job offers with the same name, does not yield the same id.', () => {
      addJobOffer('Same name');
      addJobOffer('Same name');
      const [first, second] = lastViewSnapshot!;
      assertNotEquals(first.id, second.id);
    });
    test('Updating an offer preserves its id.', () => {
      addJobOffer('Before');
      const [offer] = lastViewSnapshot!;
      board.updateJobOffer(offer.id, 'First edit');
      const [afterEdit] = lastViewSnapshot!;
      assertEquals(offer.id, afterEdit.id);
    });
  });
  describe('Job offer expiry date depends on the job offer pricing.', () => {
    test('A free job offer expires in 14 days.', () => {
      board.addJobOffer('Free offer', 'free');
      const [freeOffer] = lastViewSnapshot!;
      assertEquals(14, freeOffer.expiresInDays);
    });
    test('A paid job offer expires in 30 days.', () => {
      board.addJobOffer('Paid offer', 'paid');
      const [freeOffer] = lastViewSnapshot!;
      assertEquals(30, freeOffer.expiresInDays);
    });
  });
});

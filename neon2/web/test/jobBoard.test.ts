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
    board = new JobBoard((offers: JobOffer[]) => lastViewSnapshot = offers);
    lastViewSnapshot = null;
  });

  function lastViewSnapshotTitles(): string[] {
    return lastViewSnapshot!.map(offer => offer.title);
  }

  describe('Job board updates the view with job offers.', () => {
    test('When a new job offer is added, the job board updates the view.', () => {
      let wasCalled = false;
      const board = new JobBoard(() => {
        wasCalled = true;
      });
      board.addJobOffer('Blue offer');
      assertEquals(true, wasCalled);
    });
    test('When a new job offer is added, the job board passes the job offer title to the view.', () => {
      board.addJobOffer('Blue offer');
      assertContains('Blue offer', lastViewSnapshotTitles());
    });
    test("When a second job offer is added, the job board passes both job offers' titles to the view.", () => {
      board.addJobOffer('Red offer');
      board.addJobOffer('Blue offer');
      assertContains('Red offer', lastViewSnapshotTitles());
      assertContains('Blue offer', lastViewSnapshotTitles());
    });
    describe('Modifying an update model does not influence internal state', () => {
      test('Initially, job board has 0 job offers.', () => {
        assertEquals(0, board.count());
      });
      test('Adding job offers increases count.', () => {
        board.addJobOffer('Blue');
        assertEquals(1, board.count());
      });
      test('Clearing an update model list does not decrease count.', () => {
        board.addJobOffer('Blue offer');
        lastViewSnapshot!.length = 0;
        assertEquals(1, board.count());
      });
      test('Modifying an update model object does not pollute internal state.', () => {
        board.addJobOffer('Original name');
        const [offerSnapshot] = lastViewSnapshot!;
        offerSnapshot.title = 'Modified';
        board.updateView();
        assertEquals(['Original name'], lastViewSnapshotTitles());
      });
    });
  });
  describe('Job offer title can be updated.', () => {
    test('Given a job offer, when its title is updated, the title is no longer passed to view.', () => {
      board.addJobOffer('Before');
      const [offer] = lastViewSnapshot!;
      board.updateJobOffer(offer.id, 'After');
      assertNotContains('Before', lastViewSnapshotTitles());
    });
    test('Given a job offer, when its title is updated, the new title is passed to view.', () => {
      board.addJobOffer('Before');
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
      board.addJobOffer('Same name');
      board.addJobOffer('Same name');
      const [first, second] = lastViewSnapshot;
      assertNotEquals(first.id, second.id);
    });
    test('Updating an offer preserves its id.', () => {
      board.addJobOffer('Before');
      const [offer] = lastViewSnapshot!;
      board.updateJobOffer(offer.id, 'First edit');
      const [afterEdit] = lastViewSnapshot!;
      assertEquals(offer.id, afterEdit.id);
    });
  });
});

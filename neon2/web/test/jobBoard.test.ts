import {JobBoard} from "../src/jobBoard";
import {assertEquals, describe, test} from "./assertion";

describe('Job board', () => {
  test('When a new job offer is added, the job board updates the view', () => {
    let wasCalled = false;
    const board = new JobBoard(() => {
      wasCalled = true;
    });
    board.addJobOffer('Blue offer');
    assertEquals(true, wasCalled);
  });
  test('When a new job offer is added, the job board passes the job offer title to the view', () => {
    const board = new JobBoard((jobOfferTitle) => {
      assertEquals('Blue offer', jobOfferTitle);
    });
    board.addJobOffer('Blue offer');
  });
});

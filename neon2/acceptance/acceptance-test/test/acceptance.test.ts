import {beforeEach, describe, Dsl, saveScreenshotAfterFailedTest, test} from "./playwright";

saveScreenshotAfterFailedTest('FAILURE.png');
beforeEach(async (driver) => await driver.loadApplication());

describe('A job offer can be found in search until it expires.', () => {
  test('Given a user published a job offer, the job offer can be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Green'});
    await dsl.assertJobOfferIsSearchable({jobOfferTitle: 'Green'});
  });
});

// describe('The publishing time depends on the pricing of the job offer.', () => {
//   test('A free job offer expires after 14 days.', async (dsl: Dsl) => {
//   });
//   test('A paid job offer expires after 30 days.', async (dsl: Dsl) => {
//   });
// });

// describe('The author of a job offer can view the offer, even after the job offer has expired.', () => {
//   test('Given a job offer has expired, when a user views his job offer, then he can access the job offer.', async () => {
//   });
// });

// describe('The author of a job offer can edit the job offer even after it expires.', () => {
//   test('Given a job offer has expired, when a user edits a job offer, then the job offer is updated.', async () => {
//   });
// });

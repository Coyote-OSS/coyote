import {beforeEach, describe, Dsl, saveScreenshotAfterFailedTest, test} from "./playwright";

saveScreenshotAfterFailedTest('FAILURE.png');
beforeEach(async (driver) => await driver.loadApplication());

describe('Job offers are persisted after browser reset.', () => {
  test('Given a published a job offer, when a browser is reset, the job offer can be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Green'});
    await dsl.resetClient();
    await dsl.assertJobOfferIsSearchable({jobOfferTitle: 'Green'});
  });
  test('Given an edited a job offer, when a browser is reset, the job offer has the updated title.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Previous'});
    await dsl.updateJobOffer({title: 'Previous', updatedTitle: 'Previous updated'});
    await dsl.resetClient();
    await dsl.assertJobOfferIsSearchable({jobOfferTitle: 'Previous updated'});
  });
});

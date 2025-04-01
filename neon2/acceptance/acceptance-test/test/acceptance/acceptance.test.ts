import {Dsl} from "../../internal/dsl";
import {beforeEach, describe, saveScreenshotAfterFailedTest, test} from "../../internal/playwright";

saveScreenshotAfterFailedTest('FAILURE.png');
beforeEach(dsl => dsl.beforeEach());

describe('A job offer can be found in search until it expires.', () => {
  test('Given a user published a job offer, the job offer can be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Green'});
    await dsl.assertJobOfferIsSearchable({jobOfferTitle: 'Green'});
  });
  test('Given a job offer, when the author edits the offer, the updated job offer can be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Before'});
    await dsl.updateJobOffer({title: 'Before', updatedTitle: 'After'});
    await dsl.assertJobOfferIsSearchable({jobOfferTitle: 'After'});
  });
});

describe('The publishing time depends on the pricing of the job offer.', () => {
  test('A free job offer expires after 14 days.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Free offer', pricingType: 'free'});
    await dsl.assertJobOfferExpiresInDays({jobOfferTitle: 'Free offer', expectedExpiry: 14});
  });
  test('A paid job offer expires after 30 days.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Paid offer', pricingType: 'paid'});
    await dsl.assertJobOfferExpiresInDays({jobOfferTitle: 'Paid offer', expectedExpiry: 30});
  });
});

import {Dsl} from "../../internal/dsl";
import {beforeEach, describe, saveScreenshotAfterFailedTest, test} from "../../internal/playwright";

saveScreenshotAfterFailedTest('FAILURE.png');
beforeEach(dsl => dsl.beforeEach());

describe('A job offer can be found in search until it expires.', () => {
  test('Given a user published a job offer, the job offer can be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Green'});
    await dsl.assertJobOfferIsListed({jobOfferTitle: 'Green'});
  });
  test('Given a job offer, when the author edits the offer, the updated job offer can be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Before'});
    await dsl.updateJobOffer({title: 'Before', updatedTitle: 'After'});
    await dsl.assertJobOfferIsListed({jobOfferTitle: 'After'});
  });
  test('Given a job offer, it can be found by partial search phrase.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Big Blue Cheeseburger'});
    await dsl.publishJobOffer({title: 'Small Green Cheeseburger'});
    await dsl.searchJobOffers({searchPhrase: 'Blue'});
    await dsl.assertJobOfferIsListed({jobOfferTitle: 'Big Blue Cheeseburger'});
    await dsl.assertJobOfferIsNotListed({jobOfferTitle: 'Small Green Cheeseburger'});
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

import {Dsl} from "../../internal/dsl";
import {beforeEach, describe, saveScreenshotAfterFailedTest, test} from "../../internal/playwright";

saveScreenshotAfterFailedTest('FAILURE.png');
beforeEach(dsl => dsl.beforeEach());

describe('Job offers can be favourite.', () => {
  test('After marking an offer as favourite, it is added to favourites.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Interesting'});
    await dsl.markJobOfferAsFavourite({title: 'Interesting'});
    await dsl.assertJobOfferIsFavourite({jobOfferTitle: 'Interesting'});
  });
  test('Change is persisted in a new session.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Interesting'});
    await dsl.markJobOfferAsFavourite({title: 'Interesting'});
    await dsl.resetClient();
    await dsl.assertJobOfferIsFavourite({jobOfferTitle: 'Interesting'});
  });
});

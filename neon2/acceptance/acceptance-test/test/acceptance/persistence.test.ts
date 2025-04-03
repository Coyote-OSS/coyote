import {Dsl} from "../../internal/dsl";
import {beforeEach, describe, saveScreenshotAfterFailedTest, test} from "../../internal/playwright";

saveScreenshotAfterFailedTest('FAILURE.png');
beforeEach(dsl => dsl.beforeEach());

describe('Job offers are persisted after browser reset.', () => {
  test('Given a published a job offer, when a browser is reset, the job offer can be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Green'});
    await dsl.resetClient();
    await dsl.assertJobOfferIsListed({jobOfferTitle: 'Green'});
  });
  test('Given an edited a job offer, when a browser is reset, the job offer has the updated title.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Previous'});
    await dsl.updateJobOffer({title: 'Previous', updatedTitle: 'Previous updated'});
    await dsl.resetClient();
    await dsl.assertJobOfferIsListed({jobOfferTitle: 'Previous updated'});
  });
});

describe('Job offer in a non-free plan requires a payment.', () => {
  test('Given a paid job offer, if the payment is complete, the job offer can be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'Job offer', pricingType: 'paid', payment: 'completed'});
    await dsl.resetClient();
    await dsl.assertJobOfferIsListed({jobOfferTitle: 'Job offer'});
  });
  test('Given a paid job offer, if the payment is not made, the job offer can not be found in search.', async (dsl: Dsl) => {
    await dsl.publishJobOffer({title: 'New offer', pricingType: 'paid', payment: 'ignored'});
    await dsl.resetClient();
    await dsl.assertJobOfferIsNotListed({jobOfferTitle: 'New offer'});
  });
});

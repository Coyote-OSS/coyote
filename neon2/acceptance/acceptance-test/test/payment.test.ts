import {Dsl} from "../internal/dsl";
import {beforeEach, describe, test} from "../internal/playwright";

beforeEach(dsl => dsl.beforeEach());

describe('Payment processing.', () => {
  describe('Payment status is based on payment processing.', () => {
    test('A processed payment has status success.', async (dsl: Dsl) => {
      await dsl.initiatePayment({card: 'valid'});
      await dsl.assertPaymentStatus({expectedPaymentStatus: 'paymentComplete'});
    });
    test('Payment not attempted has status awaiting payment.', async (dsl: Dsl) => {
      await dsl.initiatePayment({card: 'declined'});
      await dsl.assertPaymentStatus({expectedPaymentStatus: 'paymentFailed'});
    });
  });

  describe('Payment notification is issued after the payment is accepted.', () => {
    test('Payment is accepted.', async (dsl: Dsl) => {
      await dsl.initiatePayment({card: 'valid'});
      await dsl.assertPaymentNotification({expectedPaymentNotification: 'accepted'});
    });
    test('Payment with an expired card is declined.', async (dsl: Dsl) => {
      await dsl.initiatePayment({card: 'expired'});
      await dsl.assertPaymentNotification({expectedPaymentNotification: 'declinedCardExpired'});
    });
    test('Payment with insufficient funds is declined.', async (dsl: Dsl) => {
      await dsl.initiatePayment({card: 'insufficientFunds'});
      await dsl.assertPaymentNotification({expectedPaymentNotification: 'declinedInsufficientFunds'});
    });
    test('Payment with a declined card is declined.', async (dsl: Dsl) => {
      await dsl.initiatePayment({card: 'declined'});
      await dsl.assertPaymentNotification({expectedPaymentNotification: 'declinedCard'});
    });
  });

  test('Payment can be made with p24.', async (dsl: Dsl) => {
    await dsl.initiatePayment({paymentMethod: 'p24'});
    await dsl.assertPaymentStatus({expectedPaymentStatus: 'paymentComplete'});
  });
});

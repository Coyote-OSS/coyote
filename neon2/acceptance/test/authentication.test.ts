import {Dsl} from "../internal/dsl";
import {beforeEach, describe, test} from "../internal/playwright";

beforeEach(dsl => dsl.beforeEach());

describe('User authentication.', () => {
  test('User is logged in.', async (dsl: Dsl) => {
    await dsl.assertUserAuthenticated({expectedState: 'loggedIn'});
  });
  test('User is a guest.', async (dsl: Dsl) => {
    await dsl.newSessionAsGuest();
    await dsl.assertUserAuthenticated({expectedState: 'guest'});
  });
  test('User can logout.', async (dsl: Dsl) => {
    await dsl.logout();
    await dsl.assertUserAuthenticated({expectedState: 'guest'});
  });
});

import {Dsl} from "../internal/dsl";
import {beforeEach, describe, test} from "../internal/playwright";

beforeEach(dsl => dsl.beforeEach());

describe('User authentication.', () => {
  test('User is logged in.', async (dsl: Dsl) => {
    await dsl.assertUserAuthenticated({expectedState: 'loggedIn'});
  });
  test('User is a guest.', async (dsl: Dsl) => {
    await dsl.logOut();
    await dsl.assertUserAuthenticated({expectedState: 'guest'});
  });
});

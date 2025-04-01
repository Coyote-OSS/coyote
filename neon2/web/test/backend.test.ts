import {JobBoardBackend} from '../src/backend';
import {assertEquals, assertNotEquals, beforeEach, describe, test} from './assertion';

describe('JobBoard backend', () => {
  let backend: JobBoardBackend;
  beforeEach(() => {
    backend = new JobBoardBackend();
  });

  function addJobOffer(title: string, plan: 'free'|'paid'): Promise<{ id, expiresInDays }> {
    return new Promise<{ id, expiresInDays }>(resolve => {
      backend.addJobOffer(title, plan, (id, expiresInDays) => {
        resolve({id, expiresInDays});
      });
    });
  }

  test('Adding two job offers with the same name, does not yield the same id.', async () => {
    const [blue, green] = await Promise.all([
      addJobOffer('Blue', 'free'),
      addJobOffer('Green', 'free'),
    ]);
    assertNotEquals(blue.id, green.id);
  });
  describe('Job offer expiry date depends on the job offer pricing.', () => {
    test('A free job offer expires in 14 days.', async () => {
      const offer = await addJobOffer('Offer', 'free');
      assertEquals(14, offer.expiresInDays);
    });
    test('A paid job offer expires in 30 days.', async () => {
      const offer = await addJobOffer('Offer', 'paid');
      assertEquals(30, offer.expiresInDays);
    });
  });
});

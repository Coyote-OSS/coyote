import {Filters, JobOffer} from "../src/filters";
import {assertEquals, assertTrue, describe, test} from "./assertion";

interface JobOfferTemplate {
  title?: string;
  publishDate?: string;
  salaryTo?: number;
  workMode?: 'stationary'|'remote';
  locations?: string[];
}

function addJobOffer(
  filters: Filters,
  {title, publishDate, salaryTo, workMode, locations}: JobOfferTemplate,
): void {
  filters.addJobOffer({
    title: title || 'Job offer',
    url: '',
    publishDate: publishDate || '2000-01-01',
    salaryTo: salaryTo || 1000,
    salaryFrom: 1000,
    workMode: workMode || 'stationary',
    locations: locations || [],
    companyName: '',
  });
}

test('job offer update listener is called, when job offer is added', () => {
  const filters = new Filters();
  let wasCalled = false;
  filters.onUpdate(jobOffer => {
    wasCalled = true;
  });
  addJobOffer(filters, {title: 'Java Developer'});
  assertTrue(wasCalled);
});

test('job offers are passed to job offer update listener', () => {
  const filters = new Filters();
  filters.onUpdate(jobOffers => {
    assertEquals('Java Developer', jobOffers[0].title);
  });
  addJobOffer(filters, {title: 'Java Developer'});
});

test('previous job offers are passed to job offer update listener', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Kotlin Developer'});
  filters.onUpdate(jobOffers => {
    assertEquals(['Kotlin Developer', 'Java Developer'], titles(jobOffers));
  });
  addJobOffer(filters, {title: 'Java Developer'});
});

test('update listener is called, when sort is updated', () => {
  const filters = new Filters();
  let wasCalled = false;
  filters.onUpdate(jobOffer => {
    wasCalled = true;
  });
  filters.sortByPublishDate();
  assertTrue(wasCalled);
});

function titles(jobOffers: JobOffer[]): string[] {
  return jobOffers.map(o => o.title);
}

test('job offers are sorted by publish date', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Java Developer', publishDate: '2000-01-01'});
  addJobOffer(filters, {title: 'Kotlin Developer', publishDate: '2002-02-02'});
  filters.onUpdate(jobOffers => {
    assertEquals(['Kotlin Developer', 'Java Developer'], titles(jobOffers));
  });
  filters.sortByPublishDate();
});

test('job offers are sorted by highest salary', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'JS Developer', salaryTo: 1000});
  addJobOffer(filters, {title: 'Java Developer', salaryTo: 1200});
  filters.onUpdate(jobOffers => {
    assertEquals(['Java Developer', 'JS Developer'], titles(jobOffers));
  });
  filters.sortByHighestSalary();
});

test('filter by search phrase calls listener', () => {
  const filters = new Filters();
  let wasCalled = false;
  filters.onUpdate(jobOffer => {
    wasCalled = true;
  });
  filters.filter('');
  assertTrue(wasCalled);
});

test('job offers are filtered by search phrase', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Python Developer'});
  addJobOffer(filters, {title: 'Java Developer'});
  filters.onUpdate(jobOffers => {
    assertEquals(['Python Developer'], titles(jobOffers));
  });
  filters.filter('Python');
});

test('job offers are filtered by search phrase, case insensitive', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Python Developer'});
  filters.onUpdate(jobOffers => {
    assertEquals(['Python Developer'], titles(jobOffers));
  });
  filters.filter('PyThOn');
});

test('job offers are filtered by minimal salary', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Python Developer', salaryTo: 1000});
  addJobOffer(filters, {title: 'Java Developer', salaryTo: 1200});
  filters.onUpdate(jobOffers => {
    assertEquals(['Java Developer'], titles(jobOffers));
  });
  filters.filterBySalary(1100);
});

test('filtering job offer by exact salary does not filter it out', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Java Developer', salaryTo: 1200});
  filters.onUpdate(jobOffers => {
    assertEquals(['Java Developer'], titles(jobOffers));
  });
  filters.filterBySalary(1200);
});

test('job offers are filtered by work mode remote', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Python Developer', workMode: 'stationary'});
  addJobOffer(filters, {title: 'Java Developer', workMode: 'remote'});
  filters.onUpdate(jobOffers => {
    assertEquals(['Java Developer'], titles(jobOffers));
  });
  filters.filterByWorkModeRemote(true);
});

test('job offers are filtered by location', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Close Job', locations: ['Europe']});
  addJobOffer(filters, {title: 'Far Job', locations: ['America']});
  filters.onUpdate(jobOffers => {
    assertEquals(['Close Job'], titles(jobOffers));
  });
  filters.filterByLocation(['Europe']);
});

test('job offers are filtered by multiple locations', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Blue', locations: ['Chicago']});
  addJobOffer(filters, {title: 'Red', locations: ['Boston']});
  addJobOffer(filters, {title: 'Green', locations: ['Detroit']});
  filters.onUpdate(jobOffers => {
    assertEquals(['Blue', 'Green'], titles(jobOffers));
  });
  filters.filterByLocation(['Chicago', 'Detroit']);
});

test('filter by location is reset after setting empty locations', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Blue', locations: ['Chicago']});
  addJobOffer(filters, {title: 'Red', locations: ['Boston']});
  filters.filterByLocation(['Chicago']);
  filters.onUpdate(jobOffers => {
    assertEquals(['Blue', 'Red'], titles(jobOffers));
  });
  filters.filterByLocation([]);
});

test('list available locations, empty if there are no job offers', () => {
  const filters = new Filters();
  assertEquals([], filters.availableLocations());
});

test('list available locations, locations of multiple offers offer', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Red', locations: ['Warsaw', 'Cracow']});
  addJobOffer(filters, {title: 'Blue', locations: ['London']});
  assertEquals(['Warsaw', 'Cracow', 'London'], filters.availableLocations());
});

test('list available locations, without duplicates', () => {
  const filters = new Filters();
  addJobOffer(filters, {title: 'Red', locations: ['Warsaw']});
  addJobOffer(filters, {title: 'Blue', locations: ['Warsaw']});
  assertEquals(['Warsaw'], filters.availableLocations());
});

test('filters available salaries are specified', () => {
  const filters = new Filters();
  assertEquals([5000, 10000, 15000, 20000, 25000, 30000], filters.availableSalaries());
});

describe('clear filters', () => {
  test('clearing filters calls listener', () => {
    const filters = new Filters();
    let wasCalled = false;
    filters.onUpdate(jobOffer => {
      wasCalled = true;
    });
    filters.clearFilters();
    assertTrue(wasCalled);
  });

  test('clears search phrase', () => {
    const filters = new Filters();
    addJobOffer(filters, {});
    filters.filter('Not matching');
    filters.onUpdate(jobOffers => assertEquals(1, jobOffers.length));
    filters.clearFilters();
  });

  test('clears locations', () => {
    const filters = new Filters();
    addJobOffer(filters, {locations: ['London']});
    filters.filterByLocation(['New York']);
    filters.onUpdate(jobOffers => assertEquals(1, jobOffers.length));
    filters.clearFilters();
  });

  test('clears minimum salary', () => {
    const filters = new Filters();
    addJobOffer(filters, {salaryTo: 1000});
    filters.filterBySalary(1100);
    filters.onUpdate(jobOffers => assertEquals(1, jobOffers.length));
    filters.clearFilters();
  });

  test('clears work mode remote', () => {
    const filters = new Filters();
    addJobOffer(filters, {workMode: 'stationary'});
    filters.filterByWorkModeRemote(true);
    filters.onUpdate(jobOffers => assertEquals(1, jobOffers.length));
    filters.clearFilters();
  });

  test('resets sort', () => {
    const filters = new Filters();
    addJobOffer(filters, {title: 'Python', salaryTo: 1000});
    addJobOffer(filters, {title: 'Ruby', salaryTo: 1200});
    filters.sortByHighestSalary();
    filters.onUpdate(jobOffers => {
      assertEquals(['Python', 'Ruby'], titles(jobOffers));
    });
    filters.clearFilters();
  });
});

describe('inspect filters', () => {
  test('read search phrase', () => {
    const filters = new Filters();
    filters.filter('foo');
    assertEquals('foo', filters.searchPhrase());
  });
});

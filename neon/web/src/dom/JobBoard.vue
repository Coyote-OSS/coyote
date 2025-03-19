<template>
  <Design.Layout>
    <Design.BannerHeading/>
    <Design.Row>
      <Design.Tabs selected="allOffers" :tabs="pageTabs" @change="redirectToMyOffers"/>
      <Design.RowEnd>
        <Design.Button icon="add" primary-outline @click="redirectToOfferForm">
          Dodaj ofertę
        </Design.Button>
      </Design.RowEnd>
    </Design.Row>
    <Design.Tile vertical>
      <Design.TextField
        v-model="state.searchPhrase"
        placeholder="Szukaj po tytule, nazwie firmy"
        test-id="jobOfferSearchPhrase"
        @submit="search">
        <Design.Button test-id="jobOfferSearch" @click="search" icon="jobOfferSearch" primary/>
      </Design.TextField>
      <div>
        <Design.Row class="max-md:hidden" vertical-center>
          <Design.Drawer scrollable nested :test-id="tagsField.testId" :icon="tagsField.icon" :title="tagsField.title">
            <Design.CheckBox v-for="tag in tagsField.values" :label="tag" v-model="state.tags[tag]"/>
          </Design.Drawer>
          <Design.Drawer scrollable nested :test-id="locationsField.testId" :icon="locationsField.icon" :title="locationsField.title">
            <Design.CheckBox v-for="location in locationsField.values" :label="location" v-model="state.locations[location]"/>
          </Design.Drawer>
          <Design.DropdownMultiple
            nested
            :test-id="workModeField.testId"
            :title="workModeField.title"
            :icon="workModeField.icon"
            :options="workModeField.options"
            v-model="state.workModes"/>
          <Design.RowEnd>
            <span @click="clearFilters" class="cursor-pointer">
              Wyczyść filtry
              <span v-if="filtersCount">
                ({{ filtersCount }})
              </span>
            </span>
          </Design.RowEnd>
        </Design.Row>
        <Design.Button
          class="md:hidden w-full"
          icon="jobOfferFilter"
          primary-outline>
          Filtruj oferty
        </Design.Button>
      </div>
    </Design.Tile>
    <Design.DropdownLabel label="Sortowanie:" class="max-md:hidden">
      <Design.Dropdown
        :title="sortField.title"
        :test-id="sortField.testId"
        :icon="sortField.icon"
        :options="sortField.options"
        v-model="state.sort"/>
    </Design.DropdownLabel>
    <ul class="space-y-2">
      <li v-for="jobOffer in jobOffers" :data-testid="jobOffer.testId">
        <Design.JobOfferListItem :job-offer="jobOffer"/>
      </li>
    </ul>
    <Design.Tile space class="mt-32 md:w-1/2 h-128 flex flex-col">
      <h1 class="text-xl mt-2">Filtruj oferty</h1>
      <Design.Divider space/>
      <div class="space-y-4">
        <Design.Drawer scrollable nested :test-id="tagsField.testId" :icon="tagsField.icon" :title="tagsField.title">
          <Design.CheckBox v-for="tag in tagsField.values" :label="tag" v-model="state.tags[tag]"/>
        </Design.Drawer>
        <Design.Drawer scrollable nested :test-id="locationsField.testId" :icon="locationsField.icon" :title="locationsField.title">
          <Design.CheckBox v-for="location in locationsField.values" :label="location" v-model="state.locations[location]"/>
        </Design.Drawer>
        <Design.DropdownMultiple
          nested
          :test-id="workModeField.testId"
          :title="workModeField.title"
          :icon="workModeField.icon"
          :options="workModeField.options"
          v-model="state.workModes"/>
      </div>
      <Design.Divider space/>
      <Design.Dropdown
        nested
        :test-id="sortField.testId"
        :icon="sortField.icon"
        :options="sortField.options"
        v-model="state.sort"/>
      <Design.Row space class="mt-auto">
        <Design.Button @click="clearFilters" test-id="jobOfferClearFilters" outline>
          Wyczyść filtry
          <span v-if="filtersCount">
            ({{ filtersCount }})
          </span>
        </Design.Button>
        <Design.Button @click="search" test-id="jobOfferSearch" primary class="flex-grow-1">
          Pokaż oferty
        </Design.Button>
      </Design.Row>
    </Design.Tile>
  </Design.Layout>
</template>

<script setup lang="ts">
import {reactive, ref, watch} from "vue";
import {initialJobOffers} from "../backendIntegration";
import {Currency, Filters, LegalForm, OrderBy, Rate, WorkMode} from "../filters";
import {Design, DropdownOption} from "./design/design";

export interface VueJobOffer {
  title: string;
  locations: string[];
  workMode: WorkMode;
  url: string;
  companyName: string|null;
  companyLogoUrl: string|null;
  salary: VueSalary|null;
  tagNames: string[];
  legalForm: LegalForm;
}

export interface VueSalary {
  to: number;
  from: number;
  currency: Currency;
  isNet: boolean;
  rate: Rate;
}

const jobOffers = ref<VueJobOffer[]>([]);
const filtersCount = ref<number>(0);

const state = reactive({
  searchPhrase: '',
  minimumSalary: 0,
  workModeRemote: false,
  workModes: [],
  workModeHybrid: false,
  sort: 'most-recent' as OrderBy,
  locations: {},
  tags: {},
});
watch(state, (): void => search());

const filters = new Filters();
filters.onUpdate(offers => {
  jobOffers.value = offers.map(offer => ({
    title: offer.title,
    url: offer.url,
    locations: offer.locations,
    workMode: offer.workMode,
    companyName: offer.companyName,
    companyLogoUrl: offer.companyLogoUrl,
    salary: salary(offer),
    tagNames: offer.tagNames,
    legalForm: offer.legalForm,
  }));
  filtersCount.value = filters.count();
});

function salary(jobOffer: JobOffer): VueSalary|null {
  if (jobOffer.salaryFrom && jobOffer.salaryTo) {
    return {
      from: jobOffer.salaryFrom!,
      to: jobOffer.salaryTo!,
      currency: jobOffer.salaryCurrency!,
      isNet: jobOffer.salaryIsNet!,
      rate: jobOffer.salaryRate!,
    };
  }
  return null;
}

initialJobOffers.forEach((jobOffer: BackendJobOffer): void => {
  filters.addJobOffer({
    title: jobOffer.title,
    url: jobOffer.url,
    salaryFrom: jobOffer.salary?.rangeFrom || null,
    salaryTo: jobOffer.salary?.rangeTo || null,
    salaryCurrency: jobOffer.salary?.currency || null,
    salaryIsNet: jobOffer.salary?.isNet || null,
    salaryRate: jobOffer.salary?.rate || null,
    publishDate: jobOffer.publishDate,
    workMode: jobOffer.workMode,
    locations: jobOffer.locations,
    companyName: jobOffer.companyName,
    companyLogoUrl: jobOffer.companyLogoUrl,
    tagNames: jobOffer.tagNames,
    legalForm: jobOffer.legalForm,
  });
});

const pageTabs = [
  {value: 'allOffers', title: 'Ogłoszenia'},
  {value: 'myOffers', title: 'Moje ogłoszenia'},
];

const sortField = {
  testId: 'jobOfferSort',
  icon: 'jobOfferFilterSort',
  options: [
    {value: 'most-recent', title: 'Najnowsze'},
    {value: 'highest-salary', title: 'Najwyższe wynagrodzenie'},
    {value: 'lowest-salary', title: 'Najniższe wynagrodzenie'},
  ],
};

const workModeFieldOptions: DropdownOption[] = [
  {icon: 'jobOfferWorkModeRemote', value: 'fullyRemote', title: 'Praca zdalna'},
  {icon: 'jobOfferWorkModeHybrid', value: 'hybrid', title: 'Praca hybrydowa'},
  {icon: 'jobOfferWorkModeStationary', value: 'stationary', title: 'Praca stacjonarna'},
];

const workModeField = {
  testId: 'jobOfferWorkMode',
  title: 'Tryb pracy',
  icon: 'jobOfferFilterWorkMode',
  options: workModeFieldOptions,
};

const tagsField = {
  testId: 'jobOfferTags',
  icon: 'jobOfferFilterTechnology',
  title: 'Technologie',
  values: filters.availableTags(),
};

const locationsField = {
  testId: 'jobOfferLocation',
  icon: 'jobOfferFilterLocation',
  title: 'Lokalizacja',
  values: filters.availableLocations(),
};

function search(): void {
  filters.filter(state.searchPhrase);
  filters.filterBySalary(state.minimumSalary);
  filters.filterByWorkMode(state.workModes);
  filters.filterByLocation(selected(state.locations));
  filters.filterByTags(selected(state.tags));
  filters.sort(state.sort);
}

function selected(selectedValues: Record<string, boolean>): string[] {
  return Object.entries(selectedValues)
    .filter(([item, selected]) => selected)
    .map(([item, selected]) => item);
}

function clearFilters(): void {
  state.searchPhrase = '';
  state.minimumSalary = 0;
  state.workModes = [];
  state.locations = {};
  state.tags = {};
  state.sort = 'most-recent';
}

function redirectToMyOffers(): void {
  window.location.href = '/Praca/Moje';
}

function redirectToOfferForm(): void {
  window.location.href = '/Praca/Oferta';
}
</script>

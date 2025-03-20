<template>
  <Design.Layout>
    <Design.BannerHeading/>
    <Design.Row>
      <Design.Tabs :tabs="tabs" v-model="state.tab"/>
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
          <Design.DropdownMultiple
            nested
            :test-id="tagsField.testId"
            :title="tagsField.title"
            :icon="tagsField.icon"
            :options="tagsField.options"
            v-model="state.tags"/>
          <Design.DropdownMultiple
            nested
            :test-id="locationsField.testId"
            :title="locationsField.title"
            :icon="locationsField.icon"
            :options="locationsField.options"
            v-model="state.locations"/>
          <Design.DropdownMultiple
            nested
            :test-id="workModeField.testId"
            :title="workModeField.title"
            :icon="workModeField.icon"
            :options="workModeField.options"
            v-model="state.workModes"/>
          <Design.DropdownMultiple
            nested
            :title="legalFormField.title"
            :icon="legalFormField.icon"
            :options="legalFormField.options"
            v-model="state.legalForms"/>
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
        <Design.JobOfferListItem
          :job-offer="jobOffer"
          @favourite-change="favourite => favouriteChange(jobOffer, favourite)"/>
      </li>
    </ul>
    <Design.Tile space class="mt-32 md:w-1/2 h-128 flex flex-col">
      <h1 class="text-xl mt-2">Filtruj oferty</h1>
      <Design.Divider space/>
      <div class="space-y-4">
        <Design.DropdownMultiple
          nested
          :test-id="tagsField.testId"
          :title="tagsField.title"
          :icon="tagsField.icon"
          :options="tagsField.options"
          v-model="state.tags"/>
        <Design.DropdownMultiple
          nested
          :test-id="locationsField.testId"
          :title="locationsField.title"
          :icon="locationsField.icon"
          :options="locationsField.options"
          v-model="state.locations"/>
        <Design.DropdownMultiple
          nested
          :test-id="workModeField.testId"
          :title="workModeField.title"
          :icon="workModeField.icon"
          :options="workModeField.options"
          v-model="state.workModes"/>
        <Design.DropdownMultiple
          nested
          :title="legalFormField.title"
          :icon="legalFormField.icon"
          :options="legalFormField.options"
          v-model="state.legalForms"/>
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
import {BackendJobOffer, initialJobOffers} from "../backendIntegration";
import {Currency, Filters, JobOffer, LegalForm, OrderBy, Rate, WorkMode} from "../filters";
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
  isFavourite: boolean;
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

function favouriteChange(jobOffer: VueJobOffer, favourite: boolean): void {
  jobOffer.isFavourite = favourite;
}

const state = reactive({
  searchPhrase: '',
  minimumSalary: 0,
  workModeRemote: false,
  workModes: [],
  legalForms: [],
  workModeHybrid: false,
  sort: 'most-recent' as OrderBy,
  locations: [],
  tags: [],
  tab: 'allOffers',
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
    isFavourite: offer.isFavourite,
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
    isFavourite: jobOffer.isFavourite,
    isMine: jobOffer.isMine,
  });
});

const tabs = [
  {value: 'allOffers', title: 'Ogłoszenia'},
  {value: 'favouriteOffers', title: 'Ulubione ogłoszenia'},
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
  {value: 'fullyRemote', title: 'Praca zdalna'},
  {value: 'hybrid', title: 'Praca hybrydowa'},
  {value: 'stationary', title: 'Praca stacjonarna'},
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
  options: toMap(filters.availableTags()),
};

const locationsField = {
  testId: 'jobOfferLocation',
  icon: 'jobOfferFilterLocation',
  title: 'Lokalizacja',
  options: toMap(filters.availableLocations()),
};

const legalFormFieldOptions: DropdownOption[] = [
  {value: 'employment', title: 'Umowa o pracę'},
  {value: 'of-mandate', title: 'Umowa zlecenie'},
  {value: 'specific-task', title: 'Umowa o dzieło'},
  {value: 'b2b', title: 'B2B'},
];

const legalFormField = {
  testId: 'jobOfferLegalForm',
  title: 'Umowa',
  icon: 'jobOfferFilterLegalForm',
  options: legalFormFieldOptions,
};

function toMap(values: string[]): DropdownOption[] {
  return values.map(value => ({value, title: value}));
}

function search(): void {
  filters.filter(state.searchPhrase);
  filters.filterBySalary(state.minimumSalary);
  filters.filterByWorkMode(state.workModes);
  filters.filterByLocation(state.locations);
  filters.filterByTags(state.tags);
  filters.filterByFavourite(state.tab === 'favouriteOffers');
  filters.filterByMine(state.tab === 'myOffers');
  filters.filterByLegalForm(state.legalForms);
  filters.sort(state.sort);
}

function clearFilters(): void {
  state.searchPhrase = '';
  state.minimumSalary = 0;
  state.workModes = [];
  state.locations = [];
  state.tags = [];
  state.sort = 'most-recent';
  state.legalForms = [];
}

function redirectToOfferForm(): void {
  window.location.href = '/Praca/Oferta';
}
</script>

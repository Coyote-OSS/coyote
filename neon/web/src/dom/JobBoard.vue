<template>
  <Design.Layout>
    <Design.BannerHeading/>
    <Design.Row>
      <Design.Tabs :tabs="tabs" v-model="state.tab"/>
      <Design.RowEnd class="max-md:hidden">
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
        <Design.Button test-id="jobOfferSearch" @click="search" icon="jobOfferSearch" primary square/>
      </Design.TextField>
      <div>
        <Design.Row class="max-md:hidden" vertical-center wrap>
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
          <Design.DropdownMultiple
            nested open-to-left
            :title="workExperienceField.title"
            :icon="workExperienceField.icon"
            :options="workExperienceField.options"
            v-model="state.workExperiences"/>
          <Design.RowEnd>
            <span @click="clearFilters" class="cursor-pointer" data-testid="jobOfferClearFilters">
              Wyczyść filtry
              <span v-if="filtersCount">
                ({{ filtersCount }})
              </span>
            </span>
          </Design.RowEnd>
        </Design.Row>
        <Design.Button class="md:hidden w-full" @click="openMobileFilters" primary-outline>
          <Icon name="jobOfferFilter" class="mr-2"/>
          {{ mobileFilterButtonText }}
        </Design.Button>
      </div>
    </Design.Tile>
    <Design.DropdownLabel label="Sortowanie:" class="max-md:hidden">
      <Design.Dropdown
        :title="sortField.title"
        :test-id="sortField.testId"
        :icon="sortField.icon"
        :options="sortField.options"
        open-to-left
        v-model="state.sort"/>
    </Design.DropdownLabel>
    <Design.Material v-if="allJobOffersAreFilteredOut" nested class="text-word text-center my-2 py-6">
      Nie znaleźliśmy żadnych ofert, pasujących do Twoich kryteriów wyszukiwania.
    </Design.Material>
    <ul class="space-y-4" v-else>
      <li v-for="jobOffer in jobOffers" :data-testid="jobOffer.testId">
        <Design.JobOfferListItem
          :job-offer="jobOffer"
          @favourite-change="favourite => favouriteChange(jobOffer, favourite)"/>
      </li>
    </ul>
    <div class="bg-tile md:hidden fixed z-[1031] top-0 left-0 w-screen h-screen" v-if="mobileFiltersVisible">
      <Design.Tile space class="flex flex-col h-full">
        <Design.Row vertical-center class="mt-2">
          <h1 class="text-xl">Filtruj oferty</h1>
          <Design.RowEnd>
            <div class="px-4 py-2 cursor-pointer" @click="closeMobileFilters">
              <Icon name="jobOfferFilterClose"/>
            </div>
          </Design.RowEnd>
        </Design.Row>
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
          <Design.DropdownMultiple
            nested
            :title="workExperienceField.title"
            :icon="workExperienceField.icon"
            :options="workExperienceField.options"
            v-model="state.workExperiences"/>
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
          <Design.Button @click="closeMobileFilters" test-id="jobOfferSearch" primary class="flex-grow-1">
            Pokaż oferty
          </Design.Button>
        </Design.Row>
      </Design.Tile>
    </div>
  </Design.Layout>
</template>

<script setup lang="ts">
import {computed, reactive, ref, watch} from "vue";
import {BackendJobOffer, initialJobOffers} from "../backendIntegration";
import {Currency, Filters, JobOffer, LegalForm, OrderBy, Rate, WorkExperience, WorkMode} from "../filters";
import Icon from "./component/Icon.vue";
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
  isNew: boolean;
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
  workModes: [],
  legalForms: [],
  workExperiences: [],
  sort: 'promoted' as OrderBy,
  locations: [],
  tags: [],
  tab: 'allOffers',
});
watch(state, (newValue): void => {
  if (newValue.tab === 'myOffers') {
    redirectToMyOffers();
  } else {
    search();
  }
});

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
    isNew: offer.isNew,
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

const allJobOffersAreFilteredOut = computed<boolean>(() => {
  if (filters.total() > 0) {
    return jobOffers.value.length === 0;
  }
  return false;
});

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
    isNew: jobOffer.isNew,
    promoted: jobOffer.promoted,
    experience: jobOffer.experience,
  });
});
search();

const tabs = [
  {value: 'allOffers', title: 'Ogłoszenia', titleShort: 'Ogłoszenia'},
  {value: 'favouriteOffers', title: 'Ulubione ogłoszenia', titleShort: 'Ulubione'},
  {value: 'myOffers', title: 'Moje ogłoszenia', titleShort: 'Moje'},
];

const sortField = {
  testId: 'jobOfferSort',
  icon: 'jobOfferFilterSort',
  options: [
    {value: 'promoted', title: 'Domyślne'},
    {value: 'most-recent', title: 'Najnowsze'},
    {value: 'highest-salary', title: 'Najwyższe wynagrodzenie'},
    {value: 'lowest-salary', title: 'Najniższe wynagrodzenie'},
  ],
};

const workModeFieldOptions: DropdownOption<WorkMode>[] = [
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
  options: dropdownOptions(filters.availableTags()),
};

const locationsField = {
  testId: 'jobOfferLocation',
  icon: 'jobOfferFilterLocation',
  title: 'Lokalizacja',
  options: dropdownOptions(filters.availableLocations()),
};

const legalFormFieldOptions: DropdownOption<LegalForm>[] = [
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

const workExperienceFieldOptions: DropdownOption<WorkExperience>[] = [
  {value: 'intern', title: 'Stażysta'},
  {value: 'junior', title: 'Junior'},
  {value: 'mid-level', title: 'Mid/Regular'},
  {value: 'senior', title: 'Senior'},
  {value: 'lead', title: 'Lead'},
  {value: 'manager', title: 'Manager'},
  {value: 'not-provided', title: 'Pozostałe'},
];

const workExperienceField = {
  title: 'Doświadczenie',
  icon: 'jobOfferFilterWorkExperience',
  options: workExperienceFieldOptions,
};

function dropdownOptions(values: string[]): DropdownOption<string>[] {
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
  filters.filterByWorkExperience(state.workExperiences);
  filters.sort(state.sort);
}

function clearFilters(): void {
  state.searchPhrase = '';
  state.minimumSalary = 0;
  state.workModes = [];
  state.locations = [];
  state.tags = [];
  state.sort = 'promoted';
  state.legalForms = [];
  state.workExperiences = [];
}

function redirectToOfferForm(): void {
  window.location.href = '/Praca/Oferta';
}

function redirectToMyOffers(): void {
  window.location.href = '/Praca/Moje';
}

const mobileFiltersVisible = ref<boolean>(false);

function openMobileFilters(): void {
  mobileFiltersVisible.value = true;
}

function closeMobileFilters(): void {
  mobileFiltersVisible.value = false;
}

const mobileFilterButtonText = computed(() => {
  if (filtersCount.value === 0) {
    return 'Filtruj oferty';
  }
  if (filtersCount.value === 1) {
    return 'Zastosowano 1 filtr';
  }
  if (filtersCount.value < 5) {
    return `Zastosowano ${filtersCount.value} filtry`;
  }
  return `Zastosowano ${filtersCount.value} filtrów`;
});
</script>

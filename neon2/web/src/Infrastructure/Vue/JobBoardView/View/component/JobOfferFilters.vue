<template>
  <Design.Tile vertical>
    <Design.TextField nested placeholder="Szukaj po tytule" v-model="state.searchPhrase">
      <Design.Button primary test-id="search" icon="jobOfferSearch" square/>
    </Design.TextField>
    <div>
      <Design.Row class="max-md:hidden" vertical-center wrap>
        <Design.DropdownMultiple
          nested
          search
          :title="tagsField.title"
          :icon="tagsField.icon"
          :options="tagsField.options"
          v-model="state.tags"/>
        <Design.DropdownMultiple
          nested
          search
          :title="locationsField.title"
          :icon="locationsField.icon"
          :options="locationsField.options"
          v-model="state.locations"/>
        <Design.DropdownMultiple
          nested
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
          <span @click="clearFilters" class="cursor-pointer">
            Wyczyść filtry
            <span v-if="filtersCount">
              ({{filtersCount}})
            </span>
          </span>
        </Design.RowEnd>
      </Design.Row>
      <Design.Button primary-outline class="md:hidden w-full" @click="openMobileFilters">
        <Icon name="jobOfferFilter" class="mr-2"/>
        {{mobileFilterButtonText}}
      </Design.Button>
    </div>
  </Design.Tile>
  <Design.DropdownLabel label="Sortowanie:" class="max-md:hidden">
    <Design.DropdownSingle
      nested-flush
      :icon="sortField.icon"
      :options="sortField.options"
      open-to-left
      v-model="state.sort"/>
  </Design.DropdownLabel>
  <div class="bg-tile md:hidden fixed z-[1031] top-0 left-0 w-screen h-dvh" v-if="mobileFiltersVisible">
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
          search
          :title="tagsField.title"
          :icon="tagsField.icon"
          :options="tagsField.options"
          v-model="state.tags"/>
        <Design.DropdownMultiple
          nested
          search
          :title="locationsField.title"
          :icon="locationsField.icon"
          :options="locationsField.options"
          v-model="state.locations"/>
        <Design.DropdownMultiple
          nested
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
      <Design.DropdownSingle
        nested
        :icon="sortField.icon"
        :options="sortField.options"
        v-model="state.sort"/>
      <Design.Row space class="mt-auto">
        <Design.Button @click="clearFilters" test-id="jobOfferClearFilters" outline>
          Wyczyść filtry
          <span v-if="filtersCount">
            ({{filtersCount}})
          </span>
        </Design.Button>
        <Design.Button @click="closeMobileFilters" test-id="jobOfferSearch" primary class="flex-grow-1">
          Pokaż oferty
        </Design.Button>
      </Design.Row>
    </Design.Tile>
  </div>
</template>

<script setup lang="ts">
import {computed, reactive, ref, watch} from "vue";
import {Design} from "../../../DesignSystem/design";
import {DropdownOption} from "../../../DesignSystem/DropdownOption";
import Icon from "../../../Icon/Icon.vue";
import {IconName} from "../../../Icon/icons";
import {Filter, FilterOptions} from "../../../../../Application/JobBoard/filter";
import {LegalForm, WorkExperience, WorkMode} from "../../../../../Domain/JobBoard/JobBoard";
import {emptyJobOfferFilter} from "./JobOfferFilters";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  filter: Filter;
  filters: FilterOptions;
}

interface Emit {
  (event: 'filter', filter: Filter): void;
}

const state = reactive<Filter>(props.filter);

watch(
  () => state,
  () => emit('filter', {...state}),
  {deep: true});

const filtersCount = computed((): number => {
  let count = 0;
  if (props.filter.searchPhrase.trim() !== '') count++;
  if (props.filter.tags.length > 0) count++;
  if (props.filter.locations.length > 0) count++;
  if (props.filter.workModes.length > 0) count++;
  if (props.filter.legalForms.length > 0) count++;
  if (props.filter.workExperiences.length > 0) count++;
  return count;
});

function clearFilters(): void {
  Object.assign(state, emptyJobOfferFilter());
}

interface Field<T extends string> {
  title: string;
  icon: IconName;
  options: DropdownOption<T>[];
}

const workModeField: Field<WorkMode> = {
  title: 'Tryb pracy',
  icon: 'jobOfferFilterWorkMode',
  options: [
    {value: 'fullyRemote', title: 'Praca zdalna'},
    {value: 'hybrid', title: 'Praca hybrydowa'},
    {value: 'stationary', title: 'Praca stacjonarna'},
  ],
};

const tagsField: Field<string> = {
  title: 'Technologie',
  icon: 'jobOfferFilterTechnology',
  options: dropdownOptions(props.filters.tags),
};

const sortField = {
  icon: 'jobOfferFilterSort' as IconName,
  options: [
    {value: 'promoted', title: 'Domyślne'},
    {value: 'most-recent', title: 'Najnowsze'},
    {value: 'highest-salary', title: 'Najwyższe wynagrodzenie'},
    {value: 'lowest-salary', title: 'Najniższe wynagrodzenie'},
  ],
};

const locationsField: Field<string> = {
  icon: 'jobOfferFilterLocation',
  title: 'Lokalizacja',
  options: dropdownOptions(props.filters.locations),
};

const legalFormField: Field<LegalForm> = {
  title: 'Umowa',
  icon: 'jobOfferFilterLegalForm',
  options: [
    {value: 'employment', title: 'Umowa o pracę'},
    {value: 'of-mandate', title: 'Umowa zlecenie'},
    {value: 'specific-task', title: 'Umowa o dzieło'},
    {value: 'b2b', title: 'B2B'},
  ],
};

const workExperienceField: Field<WorkExperience> = {
  title: 'Doświadczenie',
  icon: 'jobOfferFilterWorkExperience',
  options: [
    {value: 'intern', title: 'Stażysta'},
    {value: 'junior', title: 'Junior'},
    {value: 'mid-level', title: 'Mid/Regular'},
    {value: 'senior', title: 'Senior'},
    {value: 'lead', title: 'Lead'},
    {value: 'manager', title: 'Manager'},
    {value: 'not-provided', title: 'Pozostałe'},
  ],
};

function dropdownOptions(values: string[]): DropdownOption<string>[] {
  return values.map(value => ({value, title: value}));
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

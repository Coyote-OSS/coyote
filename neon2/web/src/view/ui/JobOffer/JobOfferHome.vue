<template>
  <div class="text-neutral2-600 space-y-3">
    <Design.Row>
      <Design.Tabs :tabs="tabs" v-model="tab" @update:model-value="changeTab"/>
      <Design.RowEnd class="max-md:hidden">
        <Design.Button
          icon="add"
          primary
          @click="screen.uiController.showForm">
          Dodaj ogłoszenie od 0zł
        </Design.Button>
      </Design.RowEnd>
    </Design.Row>
    <JobOfferFilters
      v-if="filtersVisible"
      :filter="screen.jobOfferFilter"
      :filters="screen.jobOfferFilters"
      @filter="screen.uiController.filter"/>
    <div class="flex justify-end">
      <label>
        <input type="checkbox" v-model="mapToMonths">
        Pokaż na miesiąc
      </label>
    </div>
    <JobOfferListItem
      v-for="jobOffer in screen.jobOffers"
      :key="jobOffer.id"
      :job-offer="jobOffer"
      :job-offer-url="screen.uiController.jobOfferUrl(jobOffer)"
      :map-to-months="mapToMonths"
      @select="screen.uiController.showJobOffer(jobOffer)"
      @favourite-change=""/>
    <Design.Material v-if="screen.jobOffers.length === 0" nested class="text-center my-2 py-6">
      Nie znaleźliśmy żadnych ofert, pasujących do Twoich kryteriów wyszukiwania.
    </Design.Material>
  </div>
</template>

<script setup lang="ts">
import {computed, inject, ref} from "vue";
import {JobOffer} from '../../../jobBoard';
import {JobOfferFilter} from "../../../jobOfferFilter";
import {JobOfferFilters as Filters} from "../../../main";
import {Design} from "../design/design";
import {UiController} from "../ui";
import JobOfferFilters from "./JobOfferFilters.vue";
import JobOfferListItem from "./JobOfferListItem.vue";

const screen = inject('screen') as Screen;

interface Screen {
  uiController: UiController;
  jobOffers: JobOffer[];
  jobOfferFilter: JobOfferFilter;
  jobOfferFilters: Filters;
}

const tabs = [
  {value: 'jobBoardHome', title: 'Ogłoszenia', titleShort: 'Ogłoszenia'},
  {value: 'jobBoardMine', title: 'Moje ogłoszenia', titleShort: 'Moje'},
];

type Tab = 'jobBoardHome'|'jobBoardMine';

const tab = ref<Tab>('jobBoardHome');

function changeTab(tab: string): void {
  if (tab === 'jobBoardMine') {
    screen.uiController.filterOnlyMine(true);
  } else {
    screen.uiController.filterOnlyMine(false);
  }
}

const filtersVisible = computed(() => tab.value === 'jobBoardHome');

const mapToMonths = ref<boolean>(false);
</script>

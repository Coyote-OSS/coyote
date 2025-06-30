<template>
  <div class="text-neutral2-600 space-y-3">
    <Design.Row>
      <Design.Tabs :tabs="tabs" v-model="tab" @update:model-value="changeTab"/>
      <Design.RowEnd class="max-md:hidden">
        <Design.Button
          icon="add"
          primary
          @click="service.showForm()">
          Dodaj ogłoszenie od 0zł
        </Design.Button>
      </Design.RowEnd>
    </Design.Row>
    <JobOfferFilters
      v-if="filtersVisible"
      :filter="store.jobOfferFilter"
      :filters="store.jobOfferFilters"
      @filter="f => service.filter(f)"/>
    <JobOfferListItem
      v-for="jobOffer in store.jobOffers"
      :key="jobOffer.id"
      :job-offer="jobOffer"
      :job-offer-url="service.jobOfferUrl(jobOffer)"
      @select="service.showJob(jobOffer.id)"
      @favourite="f => service.markAsFavourite(jobOffer.id, f)"/>
    <Design.Material v-if="store.jobOffers.length === 0" nested class="text-center my-2 py-6">
      Nie znaleźliśmy żadnych ofert, pasujących do Twoich kryteriów wyszukiwania.
    </Design.Material>
  </div>
</template>

<script setup lang="ts">
import {computed, ref} from "vue";
import {Design} from "../../DesignSystem/design";
import {useBoardStore} from "../boardStore";
import JobOfferFilters from "./component/JobOfferFilters.vue";
import JobOfferListItem from "./component/JobOfferListItem.vue";
import {useJobBoardService} from "./vue";

const service = useJobBoardService();
const store = useBoardStore();

const tabs = [
  {value: 'jobBoardHome', title: 'Ogłoszenia', titleShort: 'Ogłoszenia'},
  {value: 'jobBoardMine', title: 'Moje ogłoszenia', titleShort: 'Moje'},
];

type Tab = 'jobBoardHome'|'jobBoardMine';

const tab = ref<Tab>('jobBoardHome');

function changeTab(tab: string): void {
  if (tab === 'jobBoardMine') {
    service.filterOnlyMine(true);
  } else {
    service.filterOnlyMine(false);
  }
}

const filtersVisible = computed(() => tab.value === 'jobBoardHome');
</script>

<template>
  <div class="font-[Inter] text-base relative">
    <div class="space-y-3 md:w-2/3 mx-auto">
      <div class="space-y-1 card-tile-heading">
        <p class="text-sm">
          4programmers » Praca
        </p>
        <h1 class="text-xl leading-6 font-semibold">
          Oferty Pracy w IT
        </h1>
        <h2>
          Odwiedza nas ponad 150 tys. programistów miesięcznie
        </h2>
      </div>
      <div class="card-tile p-3 space-y-2">
        <div class="input-tile p-3 flex">
          <input class="outline-none flex-grow-1" placeholder="Szukaj po tytule, nazwie firmy" v-model="searchPhrase">
          <button class="button py-1 p-3 cursor-pointer" @click="search" data-testid="search">
            <Icon name="jobOfferSearch"/>
          </button>
        </div>
        <div class="md:hidden">
          <button class="button py-1 p-3 w-full rounded-lg text-sm cursor-pointer" @click="openFilters">
            <Icon name="jobOfferFilter"/>
            Filtruj oferty
          </button>
        </div>
        <div class="hidden md:flex md:flex-wrap md:space-x-2 -mb-2">
          <Dropdown class="mb-2" title="Technologia" icon="jobOfferComments"/>
          <Dropdown class="mb-2" title="Lokalizacja" icon="jobOfferComments"/>
          <Dropdown class="mb-2" title="Typ pracy" icon="jobOfferComments"/>
          <Dropdown class="mb-2" title="Wynagrodzenie" icon="jobOfferComments"/>
          <Dropdown class="mb-2" title="PLN" icon="jobOfferComments"/>
          <Dropdown class="mb-2 ml-auto" title="Sortuj według" icon="jobOfferComments"/>
        </div>
      </div>
      <JobOfferListItem v-for="jobOffer in props.jobOffers" :job-offer="jobOffer"/>
    </div>
    <div class="md:hidden">
      <FilterScreen
        v-if="filterScreenVisible"
        @close="closeFilters"
        @clear="clearFilters"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref} from "vue";
import {JobOffer} from "../application";
import Dropdown from "./component/Dropdown.vue";
import Icon from "./component/Icon.vue";
import FilterScreen from "./jobOffer/FilterScreen.vue";
import JobOfferListItem from "./jobOffer/ListItem.vue";

const props = defineProps<Props>();

interface Props {
  jobOffers: JobOffer[];
}

const searchPhrase = ref<string>('');
const filterScreenVisible = ref<boolean>(false);

function search(): void {
  window.location.search = '?' + addQueryParam(window.location.search, 'search', searchPhrase.value);
}

function addQueryParam(init: string, name: string, value: string): string {
  const params = new URLSearchParams(init);
  params.set(name, value);
  return params.toString();
}

function closeFilters(): void {
  filterScreenVisible.value = false;
}

function openFilters(): void {
  filterScreenVisible.value = true;
}

function clearFilters(): void {
}
</script>

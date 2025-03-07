<template>
  <div class="font-[Inter] text-base space-y-3 md:w-2/3 mx-auto">
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
        <button class="button py-1 p-3 cursor-pointer" @click="search">
          <Icon name="jobOfferSearch"/>
        </button>
      </div>
      <button class="button py-1 p-3 w-full rounded-lg text-sm">
        <Icon name="jobOfferFilter"/>
        Filtruj oferty
      </button>
    </div>
    <div v-for="jobOffer in props.jobOffers" class="card-tile space-y-2">
      <div class="flex flex-wrap space-x-2">
        <span v-for="location in jobOffer.locations" class="card-pill">
          <Icon name="jobOfferLocation"/>
          {{ location }}
        </span>
        <span class="card-pill">
          <Icon :name="workModeIcon(jobOffer.workMode)"/>
          {{ workModeTitle(jobOffer.workMode) }}
        </span>
        <span class="ml-auto p-2">
          <Icon name="jobOfferFavouriteChecked" v-if="jobOffer.isFavourite"/>
          <Icon name="jobOfferFavourite" v-else/>
        </span>
      </div>
      <div class="card-tile-nested">
        <div class="md:flex">
          <div class="flex space-x-2 md:flex-grow-1 items-start">
            <img class="size-12 rounded" :src="jobOffer.companyLogoUrl" v-if="jobOffer.companyLogoUrl"/>
            <div class="size-12 rounded flex-shrink-0 flex items-center justify-center job-offer-company-logo-placeholder" v-else>
              <Icon name="jobOfferLogoPlaceholder"/>
            </div>
            <p class="text-lg leading-6">
              <a :href="jobOffer.url" v-text="jobOffer.title"/>
            </p>
            <div class="badge ml-auto md:ml-2" v-if="jobOffer.isNew">Nowe</div>
          </div>
        </div>
        <template v-if="jobOffer.companyName || jobOffer.salaryFrom">
          <hr class="divider"/>
          <div class="flex items-center">
            <span v-if="jobOffer.companyName" v-text="jobOffer.companyName"/>
            <Salary
              class="ml-auto"
              v-if="jobOffer.salaryFrom"
              :range-min="jobOffer.salaryFrom"
              :range-max="jobOffer.salaryTo"
              :currency="jobOffer.salaryCurrency"
              :includes-tax="jobOffer.salaryIncludesTax"
              :settlement="jobOffer.salarySettlement"/>
          </div>
        </template>
        <template v-if="jobOffer.tagNames.length">
          <hr class="divider"/>
          <div class="flex items-center flex-wrap space-x-1">
            <span v-for="tagName in jobOffer.tagNames" class="tag text-sm mb-1" v-text="tagName"/>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref} from "vue";
import Icon, {IconName} from "./Icon.vue";
import Salary from "./jobOffer/Salary.vue";
import {JobOffer, WorkMode} from "./main";

interface Props {
  jobOffers: JobOffer[];
}

const props = defineProps<Props>();

function workModeIcon(workMode: WorkMode): IconName {
  const icons: Record<WorkMode, IconName> = {
    stationary: 'jobOfferWorkModeStationary',
    hybrid: 'jobOfferWorkModeHybrid',
    fullyRemote: 'jobOfferWorkModeRemote',
  };
  return icons[workMode];
}

function workModeTitle(workMode: WorkMode): string {
  const titles: Record<WorkMode, string> = {
    stationary: 'Stacjonarnie',
    hybrid: 'Hybryda',
    fullyRemote: 'Remote',
  };
  return titles[workMode];
}

const searchPhrase = ref<string>('');

function search(): void {
  const params = new URLSearchParams(window.location.search);
  params.set('search', searchPhrase.value);
  window.location.search = '?' + params.toString();
}
</script>

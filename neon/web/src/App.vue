<template>
  <div class="text-base space-y-3 md:w-2/3 mx-auto">
    <div class="space-y-1 card-tile">
      <p class="text-xs">
        4programmers » Praca
      </p>
      <h1 class="text-lg leading-6 font-semibold">
        Oferty Pracy w IT - Odwiedza nas ponad 150 tys. programistów miesięcznie
      </h1>
    </div>

    <div class="card-tile p-3 space-y-2">
      <div class="card-tile p-3 flex">
        <input class="outline-none flex-grow-1" placeholder="Szukaj po tytule, nazwie firmy">
        <button class="button py-1 p-3">
          <Icon name="jobOfferSearch"/>
        </button>
      </div>
      <button class="button py-1 p-3 w-full rounded-lg text-sm">
        <Icon name="jobOfferFilter"/>
        Filtruj oferty
      </button>
    </div>

    <div v-for="jobOffer in props.jobOffers" class="card-tile space-y-2">
      <div class="md:flex">
        <div class="flex space-x-2 md:flex-grow-1">
          <img class="size-12 rounded" :src="jobOffer.companyLogoUrl" v-if="jobOffer.companyLogoUrl"/>
          <div class="size-12 rounded flex-shrink-0 flex items-center justify-center job-offer-company-logo-placeholder" v-else>
            <Icon name="jobOfferLogoPlaceholder"/>
          </div>
          <p class="text-lg leading-6">
            <a :href="jobOffer.url">{{ jobOffer.title }}</a>
          </p>
        </div>
        <div class="flex items-start text-sm">
          <Salary
            v-if="jobOffer.salaryFrom"
            :range-min="jobOffer.salaryFrom"
            :range-max="jobOffer.salaryTo"
            :currency="jobOffer.salaryCurrency"
            :includes-tax="jobOffer.salaryIncludesTax"
            :settlement="jobOffer.salarySettlement"
          />
          <div class="badge ml-auto md:ml-2" v-if="jobOffer.isNew">Nowe</div>
        </div>
      </div>
      <div class="flex flex-wrap space-x-2">
        <span v-if="jobOffer.companyName">
          <Icon name="jobOfferCompany"/>
          {{ jobOffer.companyName }}
        </span>
        <span v-for="location in jobOffer.locations">
          <Icon name="jobOfferLocation"/>
          {{ location }}
        </span>
        <span>
          <Icon :name="workModeIcon(jobOffer.workMode)"/>
          {{ workModeTitle(jobOffer.workMode) }}
        </span>
      </div>
      <div class="flex items-center flex-wrap space-x-1">
        <span v-for="tagName in jobOffer.tagNames" class="tag text-sm mb-1" v-text="tagName"/>
      </div>
      <div class="flex text-xs space-x-2">
        <span>
          <Icon name="jobOfferFavouriteChecked" v-if="jobOffer.isFavourite"/>
          <Icon name="jobOfferFavourite" v-else/>
          Ulubiona
        </span>
        <span>
          <Icon name="jobOfferComments"/>
          {{ jobOffer.commentsCount }} komentarzy
        </span>
        <span class="ml-auto" v-text="jobOffer.publishDate"/>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
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
    stationary: 'Praca stacjonarna',
    hybrid: 'Praca hybrydowa',
    fullyRemote: 'Praca zdalna',
  };
  return titles[workMode];
}
</script>

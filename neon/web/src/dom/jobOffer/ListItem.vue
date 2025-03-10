<template>
  <div class="card-tile space-y-2">
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
          <div class="badge ml-auto md:ml-2" v-if="jobOffer.isNew">
            Nowe
          </div>
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
</template>

<script setup lang="ts">
import {WorkMode} from "../../application";
import Icon, {IconName} from "../component/Icon.vue";

const props = defineProps<Props>();

interface Props {
  jobOffer: JobOffer;
}

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
</script>

<template>
  <div class="text-base">
    <div class="card-tile">
      <p class="text-xs">
        4programmers » Praca
      </p>
      <h1 class="text-lg leading-6">
        Oferty Pracy w IT - Odwiedza nas ponad 150 tys. programistów miesięcznie
      </h1>
    </div>

    <div v-for="jobOffer in props.jobOffers" class="card-tile">
      <p class="text-lg">
        <a :href="jobOffer.url">{{ jobOffer.title }}</a>
      </p>
      {{
        {
          salaryFrom: jobOffer.salaryFrom,
          salaryTo: jobOffer.salaryTo,
          salaryCurrency: jobOffer.salaryCurrency,
          salaryIncludesTax: jobOffer.salaryIncludesTax,
        }
      }}
      <p class="flex flex-wrap space-x-2">
        <span v-if="jobOffer.companyName" v-text="jobOffer.companyName"/>
        <span v-for="location in jobOffer.locations">
          <Icon name="jobOfferLocation"/>
          {{ location }}
        </span>
        <span>
          <Icon :name="workModeIcon(jobOffer.workMode)"/>
          {{ workModeTitle(jobOffer.workMode) }}
        </span>
      </p>
      <div class="flex">
        <div>
          <span v-for="tagName in jobOffer.tagNames" class="tag text-sm" v-text="tagName"/>
        </div>
        <div class="badge text-sm ml-auto" v-if="jobOffer.isNew">Nowe</div>
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

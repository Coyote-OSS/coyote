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
      <p>
        {{ jobOffer.companyName }}
        {{ jobOffer.locations }}
        {{ jobOffer.workMode }}
      </p>
      <div class="flex">
        <div>
          <span v-for="tagName in jobOffer.tagNames" class="tag" v-text="tagName"/>
        </div>
        <div class="badge ml-auto" v-if="jobOffer.isNew">Nowe</div>
      </div>
      <div class="flex text-xs">
        <span>
          <Icon name="jobOfferFavouriteChecked" v-if="jobOffer.isFavourite"/>
          <Icon name="jobOfferFavourite" v-else/>
          Ulubiona
        </span>
        <span>{{ jobOffer.commentsCount }} komentarzy</span>
        <span class="ml-auto" v-text="jobOffer.publishDate"/>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon from "./Icon.vue";
import {JobOffer} from "./main";

interface Props {
  jobOffers: JobOffer[];
}

const props = defineProps<Props>();
</script>

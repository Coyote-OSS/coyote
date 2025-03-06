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
      <p>{{ jobOffer.title }}</p>
      <p>{{ jobOffer.companyName }}</p>
      <p>{{ jobOffer.commentsCount }} komentarzy</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import {JobOffer} from "./main";

interface Props {
  jobOffers: JobOffer[];
}

const props = defineProps<Props>();
</script>

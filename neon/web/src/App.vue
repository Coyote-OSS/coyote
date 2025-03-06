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
    <div v-for="jobOffer in props.jobOfferTitles" class="card-tile">
      {{ jobOffer }}
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  jobOfferTitles: string[];
}

const props = defineProps<Props>();
</script>

<template>
  <div class="card-tile">
    <p>4programmers » Praca</p>
    <h2>
      Oferty Pracy w IT - Odwiedza nas ponad 150 tys. programistów miesięcznie
    </h2>
  </div>
  <div v-for="jobOffer in props.jobOfferTitles" class="card-tile">
    {{ jobOffer }}
  </div>
</template>

<script setup lang="ts">
interface Props {
  jobOfferTitles: string[];
}

const props = defineProps<Props>();
</script>

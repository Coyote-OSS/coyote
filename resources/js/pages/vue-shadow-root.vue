<template>
  <ForumJobOffersSection class="mb-4.5" :tiles="tiles"/>
</template>

<script setup lang="ts">
import ForumJobOffersSection from '../../../web/projections/ForumJobOffers/View/ForumJobOffersSection.vue';

const tiles = window.forumJobOfferTiles;
</script>

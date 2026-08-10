<template>
  <ForumJobOffersSection :tiles="tiles"/>
</template>

<script setup lang="ts">
import ForumJobOffersSection from '../../../web/projections/ForumJobOffers/View/ForumJobOffersSection.vue';
import type {ForumJobOfferTile} from '../../../web/projections/ForumJobOffers/ViewModel/ForumJobOfferTile';

interface Props {
  tiles: ForumJobOfferTile[];
}

defineProps<Props>();
</script>

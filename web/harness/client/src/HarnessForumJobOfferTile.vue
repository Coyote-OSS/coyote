<template>
  <div :data-forum-job-offer-tile="tile.jobOfferTitle" :data-exposure="exposure">
    <ForumJobOfferTile :tile="tile" @exposure-observed="observeExposure"/>
  </div>
</template>

<script setup lang="ts">
import {ref} from 'vue';
import ForumJobOfferTile from '../../../projections/ForumJobOffers/View/ForumJobOfferTile.vue';
import type {ForumJobOfferTile as Tile} from '../../../projections/ForumJobOffers/ViewModel/ForumJobOfferTile';

interface Props {
  tile: Tile;
}

defineProps<Props>();

const exposure = ref<'pending'|'exposed'|'not-exposed'>('pending');

function observeExposure(exposed: boolean): void {
  exposure.value = exposed ? 'exposed' : 'not-exposed';
}
</script>

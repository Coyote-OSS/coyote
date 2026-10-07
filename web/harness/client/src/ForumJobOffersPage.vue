<template>
  The spacer placed the job offer tiles below the viewport.
  <div style="height: 200vh"/>
  <HarnessForumJobOfferTile
    v-for="tile in tiles"
    :key="tile.jobOfferHref"
    :tile="tile"/>
</template>

<script setup lang="ts">
import {ref} from 'vue';
import type {ForumJobOfferTile as Tile} from '../../../projections/ForumJobOffers/ViewModel/ForumJobOfferTile';
import HarnessForumJobOfferTile from './HarnessForumJobOfferTile.vue';

const tiles = ref<Tile[]>([]);

fetch('/harness/job-board/forum-job-offers')
  .then(response => response.json())
  .then((forumJobOffers: Tile[]) => tiles.value = forumJobOffers);
</script>

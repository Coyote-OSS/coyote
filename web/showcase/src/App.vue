<template>
  <DeviceGrid v-if="!isEmbedded"/>
  <DeviceViewport v-else :is-dark="isDark" :tiles="exampleForumJobOfferTiles"/>
</template>

<script setup lang="ts">
import DeviceGrid from './DeviceGrid.vue';
import DeviceViewport from './DeviceViewport.vue';
import {exampleForumJobOfferTiles} from './exampleForumJobOfferTiles';

const queryParams = new URLSearchParams(window.location.search);
const isEmbedded = queryParams.get('embed') === '1';
const isDark = queryParams.get('theme') === 'dark';
</script>

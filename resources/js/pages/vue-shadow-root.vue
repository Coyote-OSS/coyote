<template>
  <ForumJobOffersSection :class="['mb-4.5', {dark: isDark}]" :tiles="tiles"/>
</template>

<script setup lang="ts">
import {computed} from "vue";
import ForumJobOffersSection from '../../../web/projections/ForumJobOffers/View/ForumJobOffersSection.vue';

const tiles = window.forumJobOfferTiles;

interface Props {
  dark: boolean|'true'|'false'; // CustomElements serialize properties to string
}

const props = defineProps<Props>();

const isDark = computed(() => {
  if (typeof props.dark === 'boolean') {
    return props.dark;
  }
  return props.dark === 'true';
});
</script>

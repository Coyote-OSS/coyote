<template>
  <ForumJobOffersSection :class="['mb-4.5', {dark: isDark}]" :tiles="tiles"/>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {rotateJobOffers} from '../../../web/projections/ForumJobOffers/rotateJobOffers';
import ForumJobOffersSection from '../../../web/projections/ForumJobOffers/View/ForumJobOffersSection.vue';

const tiles = rotateJobOffers(window.forumJobOfferTiles, 3, new Date());

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

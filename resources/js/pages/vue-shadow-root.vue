<template>
  <ForumJobOffersSection
    :class="['mb-4.5', {dark: isDark},
      {hidden: tilesUnavailable()},
      {'md:hidden': tilesAvailableOnlyOnMobile()},
    ]"
    :tiles="tiles"
    :job-board-href="jobBoardHref"/>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {rotateJobOffers} from '../../../web/projections/ForumJobOffers/rotateJobOffers';
import ForumJobOffersSection from '../../../web/projections/ForumJobOffers/View/ForumJobOffersSection.vue';

const tiles = rotateJobOffers(window.forumJobOfferTiles, 3, new Date());
const jobBoardHref = window.forumJobOffersHref;

interface Props {
  dark: boolean|'true'|'false'; // CustomElements serialize properties to string
}

function tilesUnavailable(): boolean {
  return tiles.length === 0;
}

function tilesAvailableOnlyOnMobile(): boolean {
  return tiles.length > 0 && tiles.length < 3;
}

const props = defineProps<Props>();

const isDark = computed(() => {
  if (typeof props.dark === 'boolean') {
    return props.dark;
  }
  return props.dark === 'true';
});
</script>

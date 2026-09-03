<template>
  <main :class="[
    'min-h-screen space-y-10 px-6 py-10',
    isDark ? 'dark dark:bg-(--palette-gray-900)' : 'bg-(--palette-gray-75)',
  ]">
    <header>
      <h1 class="text-2xl font-bold text-gray-800">Coyote UI Showcase</h1>
      <p class="mt-1 text-sm text-gray-500">
        {{isDark ? 'Dark mode' : 'Light mode'}}
      </p>
    </header>
    <section>
      <h2 class="text-lg font-semibold text-gray-800">ForumJobOfferTile</h2>
      <div class="mt-4 flex flex-col gap-4">
        <VueForumJobOfferTile v-for="tile in tiles" :tile="tile"/>
      </div>
    </section>
    <section>
      <h2 class="text-lg font-semibold text-gray-800">ForumJobOffersSection</h2>
      <div class="mt-4">
        <VueForumJobOffersSection :tiles="tiles" job-board-href="#"/>
      </div>
    </section>
  </main>
</template>

<script setup lang="ts">
import VueForumJobOffersSection from '../../projections/ForumJobOffers/View/ForumJobOffersSection.vue';
import VueForumJobOfferTile from '../../projections/ForumJobOffers/View/ForumJobOfferTile.vue';
import type {ForumJobOfferTile} from '../../projections/ForumJobOffers/ViewModel/ForumJobOfferTile';

interface Props {
  isDark: boolean;
  tiles: ForumJobOfferTile[];
}

defineProps<Props>();
</script>

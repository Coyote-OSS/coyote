<template>
  <ForumJobOffersSection :class="['mb-4.5', {dark: isDark}]" :tiles="tiles"/>
</template>

<script setup lang="ts">
import ForumJobOffersSection from '../../../web/projections/ForumJobOffers/View/ForumJobOffersSection.vue';

const tiles = window.forumJobOfferTiles;
// mirrors the main site's dark-mode class (set on <html>/<body> by
// resources/js/components/navbar-toggle.ts) — read once, since this widget
// isn't expected to live-update if the user toggles theme without reloading.
const isDark = document.documentElement.classList.contains('theme-dark');
</script>

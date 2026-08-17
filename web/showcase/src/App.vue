<template>
  <div class="grid grid-cols-2">
    <main class="dark min-h-screen dark:bg-(--palette-gray-900) px-6 py-10">
      <header>
        <h1 class="text-2xl font-bold text-gray-800">Coyote UI Showcase</h1>
        <p class="mt-1 text-sm text-gray-500">Dark mode</p>
      </header>
      <section class="mt-10">
        <h2 class="text-lg font-semibold text-gray-800">ForumJobOfferTile</h2>
        <div class="mt-4 flex flex-col gap-4">
          <VueForumJobOfferTile v-for="(tile, index) in tiles" :key="index" :tile="tile"/>
        </div>
      </section>
      <section class="mt-10">
        <h2 class="text-lg font-semibold text-gray-800">ForumJobOffersSection</h2>
        <div class="mt-4">
          <VueForumJobOffersSection :tiles="tiles"/>
        </div>
      </section>
    </main>
    <main class="min-h-screen bg-(--palette-gray-75) px-6 py-10">
      <header>
        <h1 class="text-2xl font-bold text-gray-800">Coyote UI Showcase</h1>
        <p class="mt-1 text-sm text-gray-500">Light mode</p>
      </header>
      <section class="mt-10">
        <h2 class="text-lg font-semibold text-gray-800">ForumJobOfferTile</h2>
        <div class="mt-4 flex flex-col gap-4">
          <VueForumJobOfferTile v-for="(tile, index) in tiles" :key="index" :tile="tile"/>
        </div>
      </section>
      <section class="mt-10">
        <h2 class="text-lg font-semibold text-gray-800">ForumJobOffersSection</h2>
        <div class="mt-4">
          <VueForumJobOffersSection :tiles="tiles"/>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import type {ForumJobOfferTile} from '../../projections/ForumJobOffers/ViewModel/ForumJobOfferTile';
import VueForumJobOfferTile from '../../projections/ForumJobOffers/View/ForumJobOfferTile.vue';
import VueForumJobOffersSection from '../../projections/ForumJobOffers/View/ForumJobOffersSection.vue';

const logoPlaceholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64'%3E%3Crect width='64' height='64' rx='8' fill='%2394a3b8'/%3E%3C/svg%3E";

const tiles: ForumJobOfferTile[] = [
  // standard: regular company name and logo, regular title, a salary, two tags (one with a logo, one without)
  {
    companyName: 'Acme Corp',
    companyLogoUrl: logoPlaceholder,
    jobOfferHref: '#',
    jobOfferTitle: 'Senior PHP Developer',
    headerPills: ['Remote', 'B2B'],
    salary: '18 000 - 24 000 PLN',
    salaryDisclosed: true,
    isNew: false,
    technologyTags: [
      {name: 'PHP', logoUrl: logoPlaceholder},
      {name: 'Symfony', logoUrl: null},
    ],
  },
  // empty: every optional field is short or omitted, salary falls back to the same string the real presenter uses
  {
    companyName: 'Nova',
    companyLogoUrl: null,
    jobOfferHref: '#',
    jobOfferTitle: 'PHP Dev',
    headerPills: [],
    salary: 'Nie podano $$$',
    salaryDisclosed: false,
    isNew: false,
    technologyTags: [],
  },
  // full: very long company name and title, logo present, ten technology tags, the only one with the "Nowe" badge
  {
    companyName: 'International Multinational Technology & Consulting Solutions Group Corporation',
    companyLogoUrl: logoPlaceholder,
    jobOfferHref: '#',
    jobOfferTitle: 'Senior Staff Principal Full-Stack Software Engineering Architect Specializing in Distributed Systems, Cloud Infrastructure and Platform Reliability',
    headerPills: ['Warszawa, +1', 'Hybrid'],
    salary: '25 000 - 35 000 PLN',
    salaryDisclosed: true,
    isNew: true,
    technologyTags: [
      {name: 'PHP', logoUrl: logoPlaceholder},
      {name: 'Symfony', logoUrl: null},
      {name: 'Laravel', logoUrl: logoPlaceholder},
      {name: 'Vue', logoUrl: null},
      {name: 'TypeScript', logoUrl: logoPlaceholder},
      {name: 'Docker', logoUrl: null},
      {name: 'Kubernetes', logoUrl: logoPlaceholder},
      {name: 'AWS', logoUrl: null},
      {name: 'PostgreSQL', logoUrl: logoPlaceholder},
      {name: 'Redis', logoUrl: null},
    ],
  },
];
</script>

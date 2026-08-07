<template>
  <div v-if="props.tiles.length > 0" class="card card-post d-flex flex-row flex-nowrap gap-3 p-3" style="overflow: hidden;">
    <a v-for="tile in props.tiles"
       :key="tile.jobOfferHref"
       :href="tile.jobOfferHref"
       class="d-block text-decoration-none flex-shrink-0 border rounded p-3"
       style="min-height: 220px; min-width: 240px;">
      <div class="d-flex align-items-center mb-2">
        <img v-if="tile.companyLogoUrl" :src="tile.companyLogoUrl" :alt="tile.companyName"
             style="width: 32px; height: 32px; object-fit: contain;" class="me-2">
        <div v-else class="neon-placeholder d-flex align-items-center justify-content-center me-2"
             style="width: 32px; height: 32px;">
          <vue-icon name="jobOfferMissingLogo"></vue-icon>
        </div>
        <span>{{tile.jobOfferTitle}}</span>
      </div>
      <div class="mb-2">{{tile.companyName}}</div>
      <div class="mb-2">{{tile.salary}}</div>
      <div v-if="tile.headerPills.length > 0" class="d-flex align-items-center flex-wrap gap-1 mb-2">
        <span v-for="pill in tile.headerPills" :key="pill" class="d-inline-flex align-items-center border rounded px-1">{{pill}}</span>
      </div>
      <div class="d-flex flex-wrap gap-1">
        <span v-for="tag in tile.technologyTags" :key="tag.name" class="d-inline-flex align-items-center border rounded px-1">
          <img v-if="tag.logoUrl" :src="tag.logoUrl" style="width: 16px; height: 16px;" class="me-1">
          {{tag.name}}
        </span>
      </div>
    </a>
  </div>
</template>

<script setup lang="ts">
import {ForumJobOfferTile} from "../../../../projections/ForumJobOffers/ViewModel/ForumJobOfferTile";
import VueIcon from "../icon";

interface Props {
  tiles: ForumJobOfferTile[];
}

const props = defineProps<Props>();
</script>

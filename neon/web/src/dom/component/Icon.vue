<template>
  <FontAwesomeIcon
    :class="fontAwesomeClasses"
    :fixed-width="props.monospace"/>
</template>

<script setup lang="ts">
import {computed} from "vue";
import FontAwesomeIcon from "../internal/FontAwesomeIcon.vue";

export type IconName = keyof typeof icons;

const props = defineProps<Props>();

interface Props {
  name: IconName;
  monospace?: boolean;
}

const jobOfferLocation = 'fa-light fa-location-dot';
const jobOfferWorkModeStationary = 'fa-light fa-suitcase';
const icons = {
  dropdownClosed: 'fa-light fa-chevron-down',
  jobOfferFavourite: 'fa-light fa-star',
  jobOfferFavouriteChecked: 'fa-solid fa-star',
  jobOfferWorkModeRemote: 'fa-solid fa-wifi',
  jobOfferWorkModeHybrid: 'fa-light fa-globe-wifi',
  jobOfferWorkModeStationary,
  jobOfferComments: 'fa-light fa-comment',
  jobOfferLocation,
  jobOfferCompany: 'fa-light fa-building',
  jobOfferLogoPlaceholder: 'fa-light fa-laptop-code',
  jobOfferSearch: 'fa-light fa-magnifying-glass',
  jobOfferFilter: 'fa-light fa-filter',
  jobOfferFilterClose: 'fa-light fa-close',
  jobOfferFilterTechnology: 'fa-light fa-code',
  jobOfferFilterLocation: jobOfferLocation,
  jobOfferFilterWorkMode: jobOfferWorkModeStationary,
  jobOfferFilterSalary: 'fa-light fa-sack-dollar',
  jobOfferFilterCurrency: 'fa-light fa-coins',
  jobOfferFilterSort: 'fa-light fa-bars-sort',
};

const fontAwesomeClasses = computed<string>((): string => icons[props.name]);
</script>

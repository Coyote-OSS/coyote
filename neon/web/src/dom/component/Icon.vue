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
const jobOfferWorkModeHybrid = 'fa-light fa-globe-wifi';
const jobOfferWorkModeRemote = 'fa-solid fa-wifi';
const check = 'fa-light fa-check';
const plus = 'fa-light fa-plus';

const icons = {
  dropdownClosed: 'fa-light fa-chevron-down',
  dropdownOptionSelected: check,
  checkboxChecked: check,
  add: plus,
  jobOfferFavourite: 'fa-light fa-star',
  jobOfferFavouriteChecked: 'fa-solid fa-star',
  jobOfferWorkModeRemote,
  jobOfferWorkModeHybrid,
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
  jobOfferFilterWorkMode: jobOfferWorkModeHybrid,
  jobOfferFilterSalary: 'fa-light fa-sack-dollar',
  jobOfferFilterCurrency: 'fa-light fa-coins',
  jobOfferFilterSort: 'fa-light fa-bars-sort',
};

const fontAwesomeClasses = computed<string>((): string => icons[props.name]);
</script>

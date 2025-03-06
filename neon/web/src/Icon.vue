<template>
  <FontAwesomeIcon :class="fontAwesomeClasses"/>
</template>

<script setup lang="ts">
import {computed} from "vue";
import FontAwesomeIcon from "./FontAwesomeIcon.vue";

export type IconName = keyof typeof icons;

const props = defineProps<Props>();

interface Props {
  name: IconName;
}

const icons = {
  'jobOfferFavourite': 'fa-light fa-star',
  'jobOfferFavouriteChecked': 'fa-solid fa-star',
  'jobOfferWorkModeRemote': 'fa-solid fa-wifi',
  'jobOfferWorkModeHybrid': 'fa-light fa-globe-wifi',
  'jobOfferWorkModeStationary': 'fa-light fa-suitcase',
  'jobOfferComments': 'fa-light fa-comment',
  'jobOfferLocation': 'fa-light fa-location-dot',
  'jobOfferCompany': 'fa-light fa-building',
  'jobOfferLogoPlaceholder': 'fa-light fa-laptop-code',
  'jobOfferSearch': 'fa-light fa-magnifying-glass',
  'jobOfferFilter': 'fa-light fa-filter',
};

const fontAwesomeClasses = computed<string>((): string => icons[props.name]);
</script>

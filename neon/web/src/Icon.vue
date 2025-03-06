<template>
  <FontAwesomeIcon class="fa-fw" :class="fontAwesomeClasses"/>
</template>

<script setup lang="ts">
import {computed} from "vue";
import FontAwesomeIcon from "./FontAwesomeIcon.vue";

type IconName = 'jobOfferFavourite'|'jobOfferFavouriteChecked';

const props = defineProps<Props>();

interface Props {
  name: IconName;
}

const icons: Record<IconName, string> = {
  'jobOfferFavourite': 'fa-light fa-star',
  'jobOfferFavouriteChecked': 'fa-solid fa-star',
};

const fontAwesomeClasses = computed<string>((): string => icons[props.name]);
</script>

<template>
  <i :class="icons[name]"/>
</template>

<script lang="ts">
export const icons = {
  location: 'fa-light fa-location-dot',
  favourite: 'fa-light fa-star',
  company: 'fa-light fa-building',
  'chevron-right': 'fa-light fa-chevron-right',
  viewportDesktop: 'fa-light fa-display',
  viewportMobile: 'fa-light fa-mobile-screen-button',
};

export type IconName = keyof typeof icons;
</script>

<script setup lang="ts">
import './fontAwesome.css';
import './fontAwesomeIcons.css';

interface Props {
  name: IconName;
}

defineProps<Props>();
</script>

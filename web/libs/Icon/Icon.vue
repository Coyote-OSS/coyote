<template>
  <i :class="icons[name]"/>
</template>

<script lang="ts">
export const icons = {
  location: 'fa-light fa-location-dot',
  star: 'fa-light fa-star',
  building: 'fa-light fa-building',
  'chevron-right': 'fa-light fa-chevron-right',
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

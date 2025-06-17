<template>
  <Tile vertical space :big-space="props.space" :class="border">
    <p class="text-lg font-semibold" v-text="props.title" v-if="props.title"/>
    <slot/>
  </Tile>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Tile from "./Tile.vue";

const props = defineProps<Props>();

interface Props {
  title?: string;
  space?: boolean;
  border?: boolean;
}

const border = computed(() => {
  if (props.border) {
    return 'rounded-lg border border-tile-border';
  }
  return '';
});
</script>

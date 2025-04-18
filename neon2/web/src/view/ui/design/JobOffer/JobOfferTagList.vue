<template>
  <span class="flex flex-wrap gap-1 items-center">
    <span v-for="tag in visibleTags" v-text="tag" class="bg-tag px-1 py-0.5 rounded text-sm"/>
    <span v-if="hiddenTags">+{{ hiddenTags }}</span>
  </span>
</template>

<script setup lang="ts">
import {computed} from "vue";

interface Props {
  tagNames: string[];
  max: number;
}

const props = defineProps<Props>();

const visibleTags = computed(() => props.tagNames.slice(0, props.max));
const hiddenTags = computed(() => Math.max(0, props.tagNames.length - props.max));
</script>

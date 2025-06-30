<template>
  <div class="flex flex-wrap gap-2 items-center">
    <div
      v-for="tag in props.tags"
      class="flex items-center bg-neutral2-050 text-neutral2-500 text-sm rounded px-2 py-1">
      <span class="mr-1.5" v-text="tag.tagName"/>
      <span class="-ml-1.5" v-if="tag.priority == 0"/>
      <div
        v-else
        v-for="dot in [1, 2, 3]"
        class="size-1.5 rounded-full ml-0.5"
        :class="dot <= tag.priority ? 'bg-green-500' : 'bg-neutral2-200'"/>
    </div>
  </div>
</template>

<script setup lang="ts">

import {JobOfferTag} from "../../../../../Domain/JobBoard/JobBoard";

const props = defineProps<Props>();

interface Props {
  tags: JobOfferTag[];
}
</script>

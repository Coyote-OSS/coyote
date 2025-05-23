<template>
  <div class="flex flex-wrap gap-4 items-center">
    <div v-for="tag in props.tags">
      <span v-text="tag.tagName" class="bg-tag px-1 py-0.5 rounded text-sm mr-2"/>
      <JobOfferTagPriority :priority="tag.priority"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {Tag} from "../../../main";
import JobOfferTagPriority from "./JobOfferTagPriority.vue";

const props = defineProps<Props>();

interface Props {
  tags: Tag[];
}
</script>

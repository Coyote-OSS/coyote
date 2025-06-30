<template>
  <span class="flex flex-wrap gap-1 items-center">
    <TagName v-for="tag in visibleTags" v-text="tag"/>
    <span v-if="hiddenTags">+{{hiddenTags}}</span>
  </span>
</template>

<script setup lang="ts">
import {computed} from "vue";
import TagName from "../../../DesignSystem/TagName.vue";

interface Props {
  tagNames: string[];
  max: number;
}

const props = defineProps<Props>();

const visibleTags = computed(() => props.tagNames.slice(0, props.max));
const hiddenTags = computed(() => Math.max(0, props.tagNames.length - props.max));
</script>

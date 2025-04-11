<template>
  <div class="flex" :class="[
    props.space ? 'gap-4' : 'gap-2', {
      'flex-wrap': props.wrap,
      'items-center': props.verticalCenter,
      'justify-between': props.apart,
    }]">
    <slot/>
  </div>
</template>

<script setup lang="ts">
interface Props {
  space?: boolean;
  verticalCenter?: boolean;
  apart?: boolean;
  wrap?: boolean;
}

const props = defineProps<Props>();
</script>

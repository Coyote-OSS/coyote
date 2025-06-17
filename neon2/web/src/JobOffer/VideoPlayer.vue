<template>
  <iframe :src="props.youtubeUrl" allowfullscreen/>
</template>

<script setup lang="ts">
const props = defineProps<Props>();

interface Props {
  youtubeUrl: string;
}
</script>

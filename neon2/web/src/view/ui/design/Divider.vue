<template>
  <hr class="text-divider" :class="props.space ? 'my-6' : 'my-2'"/>
</template>

<script setup lang="ts">
interface Props {
  space?: boolean;
}

const props = defineProps<Props>();
</script>

<template>
  <div
    class="text-red-500 text-sm mt-1 mb-4"
    data-testid="errorMessage"
    v-text="props.message"/>
</template>

<script setup lang="ts">
interface Props {
  message: string;
}

const props = defineProps<Props>();
</script>

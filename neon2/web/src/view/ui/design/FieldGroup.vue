<template>
  <div class="flex-1">
    <label>
      <span v-text="props.label" class="mb-1 block font-medium"/>
      <slot/>
    </label>
  </div>
</template>

<script setup lang="ts">
interface Props {
  label: string;
}

const props = defineProps<Props>();
</script>

<template>
  <div class="flex items-center">
    <span class="ml-auto mr-3" v-text="props.label"/>
    <slot/>
  </div>
</template>

<script setup lang="ts">
interface Props {
  label: string;
}

const props = defineProps<Props>();
</script>

<template>
  <div v-text="props.value" :class="[
    'absolute -top-1 -right-1',
    'rounded size-5 leading-5 text-center text-xs',
    colors,
  ]"/>
</template>

<script setup lang="ts">
import {computed} from "vue";

const props = defineProps<Props>();

interface Props {
  value: string|number;
  important?: boolean;
}

const colors = computed(() => {
  if (props.important) {
    return 'bg-red-500 text-white';
  }
  return 'bg-primary text-on-primary';
});
</script>

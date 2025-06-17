<template>
  <div class="ml-auto" :style="rowFlexStyle">
    <slot/>
  </div>
</template>

<script setup lang="ts">
import {computed, StyleValue} from "vue";

interface Props {
  inline?: boolean;
}

const props = defineProps<Props>();

const rowFlexStyle = computed<StyleValue>(() => {
  if (props.inline) {
    return {
      display: 'flex',
      flexWrap: 'inherit',
      gap: 'inherit',
      alignItems: 'inherit',
    };
  }
  return {};
});
</script>

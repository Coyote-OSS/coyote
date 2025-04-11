<template>
  <div class="ml-auto" :style="rowFlexStyle">
    <slot/>
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";

interface Props {
  inline?: boolean;
}

const props = defineProps<Props>();

const rowFlexStyle = computed<object>(() => {
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

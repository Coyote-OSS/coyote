<template>
  <div :class="tileClass">
    <slot/>
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";

const props = defineProps<Props>();

interface Props {
  nested?: boolean;
  space?: boolean;
  round?: MaterialRound;
}

export type MaterialRound = 'regular'|'large'|'full';

const tileClass = computed(() => [background(), rounded(), padding()]);

function background(): string {
  return props.nested ? 'bg-tile-nested' : 'bg-tile';
}

function rounded(): string {
  if (props.round === 'full') {
    return 'rounded-3xl';
  }
  if (props.round === 'large') {
    return 'rounded-2xl';
  }
  return 'rounded-lg';
}

function padding(): string {
  return props.space ? 'p-4' : 'p-2';
}
</script>

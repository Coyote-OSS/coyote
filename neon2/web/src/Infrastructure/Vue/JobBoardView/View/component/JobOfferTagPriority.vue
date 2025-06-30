<template>
  <span class="inline-flex gap-2">
    <span
      v-for="priority in priorities"
      class="size-2.5 rounded-full"
      @click="select(priority)"
      :class="[
        props.priority >= priority ? 'bg-green-500' : 'border border-neutral2-300',
        props.selectable ? 'cursor-pointer' : '',
      ]"/>
  </span>
</template>

<script setup lang="ts">

import {JobOfferTagPriority} from "../../../../../Domain/JobBoard/JobOfferTagPriority";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

const priorities: JobOfferTagPriority[] = [1, 2, 3];

interface Props {
  priority: JobOfferTagPriority;
  selectable?: boolean;
}

interface Emit {
  (event: 'select', priority: JobOfferTagPriority): void;
}

function select(priority: JobOfferTagPriority): void {
  emit('select', priority);
}
</script>

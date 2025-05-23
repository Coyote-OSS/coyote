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
import {BackendJobOfferTagPriority} from "../../../backend";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

const priorities: BackendJobOfferTagPriority[] = [1, 2, 3];

interface Props {
  priority: BackendJobOfferTagPriority;
  selectable?: boolean;
}

interface Emit {
  (event: 'select', priority: BackendJobOfferTagPriority): void;
}

function select(priority: BackendJobOfferTagPriority): void {
  emit('select', priority);
}
</script>

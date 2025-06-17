<template>
  <div
    class="flex-grow-1 basis-1 text-center cursor-pointer rounded-lg py-8 border"
    :class="tileClass"
    v-text="props.title"
    @click="emit('select')"/>
</template>

<script setup lang="ts">
import {computed} from "vue";

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Emit {
  (event: 'select'): void;
}

interface Props {
  title: string;
  selected: boolean;
}

const tileClass = computed(() => {
  if (props.selected) {
    return 'text-green-800 bg-green-050 border-green-500';
  }
  return 'text-neutral2-500 bg-neutral2-050 border-transparent';
});
</script>

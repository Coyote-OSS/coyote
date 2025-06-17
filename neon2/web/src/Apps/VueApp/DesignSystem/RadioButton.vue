<template>
  <div class="flex cursor-pointer" @click="select">
    <div
      class="size-5 rounded-xl mr-2 flex-shrink-0"
      :class="props.selected ? 'border-5 border-green-500' : 'border-1 border-neutral2-400'"/>
    {{props.title}}
  </div>
</template>

<script setup lang="ts">
const props = defineProps<Props>();
const emit = defineEmits<Emit>();

interface Props {
  selected: boolean;
  title: string;
}

interface Emit {
  (event: 'select'): void;
}

function select(): void {
  emit('select');
}
</script>

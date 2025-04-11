<template>
  <div class="flex">
    <div
      v-for="tab in props.tabs"
      @click="select(tab)"
      class="px-4 py-3"
      :class="[
        selected(tab) ? 'border-b-2 border-primary' : 'text-neutral-400 dark:text-inherit',
        selected(tab) ? 'cursor-default' : 'cursor-pointer',
      ]">
      <span class="max-md:hidden" v-text="tab.title"/>
      <span class="md:hidden" v-text="tab.titleShort"/>
    </div>
  </div>
</template>

<script setup lang="ts" generic="T">
interface Props {
  tabs: Tab<T>[];
}

export interface Tab<T> {
  value: T;
  title: string;
  titleShort: string;
}

const props = defineProps<Props>();
const emit = defineEmits(['change']);
const model = defineModel<T>();

function select(tab: Tab<T>): void {
  model.value = tab.value;
}

function selected(tab: Tab<T>): boolean {
  return tab.value === model.value;
}
</script>

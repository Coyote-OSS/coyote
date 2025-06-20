<template>
  <div class="rounded-2xl border border-tile-border p-6 flex items-center justify-between">
    <template v-for="(step, index) in props.steps" :key="index">
      <Step
        :title="step.title"
        :mode="stepMode(index)"
        :counter="index+1"/>
      <div class="w-9.5 h-px bg-neutral2-300" v-if="index < props.steps.length - 1"/>
    </template>
  </div>
</template>

<script setup lang="ts" generic="U extends string|number">
import {computed} from "vue";
import {DrawerOption} from "./DropdownSingle.vue";
import Step, {StepMode} from "./Step.vue";

const props = defineProps<Props>();

interface Props {
  steps: DrawerOption<U>[];
  step: U;
}

function stepMode(index: number): StepMode {
  if (index === currentIndex.value) {
    return 'current';
  }
  if (index < currentIndex.value) {
    return 'previous';
  }
  return 'next';
}

const currentIndex = computed<number>(() => {
  return props.steps.findIndex(step => step.value === props.step);
});
</script>

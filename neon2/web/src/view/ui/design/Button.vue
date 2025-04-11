<template>
  <button
    @click="click"
    class="cursor-pointer rounded-lg"
    :class="[variantClass, sizeClass]"
    :data-testid="props.testId">
    <Icon v-if="props.icon" :name="props.icon"/>
    <slot/>
  </button>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon, {IconName} from "../icons/Icon.vue";

const emit = defineEmits(['click']);

interface Props {
  testId?: string;
  icon?: IconName;
  primary?: boolean;
  primaryOutline?: boolean;
  outline?: boolean;
  square?: boolean;
}

const props = defineProps<Props>();

function click(): void {
  emit('click');
}

const variantClass = computed(() => {
  if (props.primary) {
    return 'bg-primary text-on-primary';
  }
  if (props.primaryOutline) {
    return 'text-primary border border-primary';
  }
  if (props.outline) {
    return 'text-neutral-600 dark:text-neutral-050 border border-current';
  }
  return '';
});

const sizeClass = computed(() => {
  if (props.square) {
    return 'size-8.5';
  }
  return 'px-4 py-2';
});
</script>

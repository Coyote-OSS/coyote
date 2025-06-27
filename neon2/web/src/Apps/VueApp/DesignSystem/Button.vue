<template>
  <button
    @click="click"
    class="rounded-lg whitespace-nowrap"
    :disabled="props.disabled"
    :class="[variantClass, sizeClass, cursorClass, {'w-full': props.fullWidth}]"
    :data-testid="props.testId">
    <Icon v-if="props.icon" :name="props.icon" :class="{'mr-2': !props.square}"/>
    <template v-if="props.title">{{props.title}}</template>
    <slot/>
  </button>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon from "../Icon/Icon.vue";
import {IconName} from "../Icon/icons";

const emit = defineEmits(['click']);

interface Props {
  testId?: string;
  icon?: IconName;
  title?: string;
  primary?: boolean;
  primaryOutline?: boolean;
  outline?: boolean;
  square?: boolean;
  fullWidth?: boolean;
  disabled?: boolean;
  cursorWait?: boolean;
}

const props = defineProps<Props>();

function click(): void {
  if (!props.disabled) {
    emit('click');
  }
}

const variantClass = computed(() => {
  if (props.primary) {
    if (props.disabled) {
      return 'text-neutral2-400 bg-neutral2-200';
    }
    return 'bg-primary text-on-primary border border-transparent';
  }
  if (props.primaryOutline) {
    return 'text-primary border border-primary';
  }
  if (props.outline) {
    return 'text-neutral2-800 border border-neutral2-200';
  }
  return 'bg-tile';
});

const cursorClass = computed(() => {
  if (props.disabled) {
    return 'cursor-not-allowed';
  }
  if (props.cursorWait) {
    return 'cursor-progress';
  }
  return 'cursor-pointer';
});

const sizeClass = computed(() => {
  if (props.square) {
    return 'size-8.5';
  }
  return 'px-4 py-2';
});
</script>

<template>
  <div :class="tileClass" :data-testid="props.testId">
    <Icon v-if="props.icon" :name="props.icon" class="mr-2"/>
    {{ props.text }}
    <slot/>
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon, {IconName} from "../component/Icon.vue";

const props = defineProps<Props>();

interface Props {
  nested?: boolean;
  icon?: IconName;
  testId?: string;
  nestedPill?: boolean;
  space?: boolean;
  desktopSpace?: boolean;
  vertical?: boolean;
  text?: string;
}

const tileClass = computed(() => {
  return [
    props.nested || props.nestedPill ? 'bg-tile-nested' : 'bg-tile',
    rounded(),
    padding(),
    verticalSpacing(),
    fontSize(),
    wrapping(),
  ];
});

function rounded(): string {
  if (props.nestedPill) {
    return 'rounded-3xl';
  }
  if (!props.nested) {
    return 'rounded-xl';
  }
  return 'rounded-lg';
}

function padding(): string {
  if (props.nestedPill) {
    return 'py-2 px-3';
  }
  if (props.desktopSpace) {
    return 'p-2 md:p-4';
  }
  if (props.space) {
    return 'p-4';
  }
  return 'p-2';
}

function verticalSpacing(): string {
  if (props.vertical) {
    return props.space ? 'space-y-4' : 'space-y-2';
  }
  return '';
}

function fontSize(): string {
  if (props.nestedPill) {
    return 'text-sm';
  }
  return '';
}

function wrapping(): string {
  if (props.nestedPill) {
    return 'whitespace-nowrap';
  }
  return '';
}
</script>

<template>
  <Design.Material
    :nested="props.nested || props.nestedPill"
    :round="rounded"
    :class="tileClass"
    :data-testid="props.testId">
    <Icon v-if="props.icon" :name="props.icon" class="mr-2"/>
    {{props.text}}
    <slot/>
  </Design.Material>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon, {IconName} from "../icons/Icon.vue";
import {Design} from "./design";
import {MaterialRound} from "./Material.vue";

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

const tileClass = computed(() => [padding(), verticalSpacing(), fontSize(), wrapping()]);

const rounded = computed((): MaterialRound => {
  if (props.nestedPill) {
    return 'full';
  }
  if (props.nested) {
    return 'regular';
  }
  return 'large';
});

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
  return props.nestedPill ? 'text-sm' : '';
}

function wrapping(): string {
  return props.nestedPill ? 'whitespace-nowrap' : '';
}
</script>

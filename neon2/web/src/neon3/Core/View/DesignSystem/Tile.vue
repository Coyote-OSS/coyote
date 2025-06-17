<template>
  <Material
    :nested="props.nested || props.nestedPill"
    :round="rounded"
    :class="tileClass"
    :href="props.href"
    :data-testid="props.testId">
    <Icon v-if="props.icon" :name="props.icon" class="mr-2"/>
    {{props.text}}
    <slot/>
  </Material>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon from "../Icon/Icon.vue";
import {IconName} from "../Icon/icons";
import Material, {MaterialRound} from "./Material.vue";

const props = defineProps<Props>();

interface Props {
  nested?: boolean;
  icon?: IconName;
  testId?: string;
  nestedPill?: boolean;
  space?: boolean;
  bigSpace?: boolean;
  desktopSpace?: boolean;
  vertical?: boolean;
  text?: string;
  shadow?: boolean;
  href?: string;
}

const tileClass = computed(() => [padding(), verticalSpacing(), fontSize(), wrapping(), shadow(), cursor()]);

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
  if (props.bigSpace) {
    return 'p-4 md:p-6';
  }
  if (props.space) {
    return 'p-4';
  }
  return 'p-2';
}

function verticalSpacing(): string {
  if (props.vertical) {
    if (props.bigSpace) {
      return 'space-y-6';
    }
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

function shadow(): string {
  return props.shadow ? 'shadow-2xl' : '';
}

function cursor(): string {
  return props.clickable ? 'cursor-pointer' : '';
}
</script>

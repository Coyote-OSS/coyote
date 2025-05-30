<template>
  <Material
    :nested="props.nested"
    class="relative"
    :class="[borderClass, disabledClass]"
    :disabled="props.disabled"
    @click="toggle">
    <div class="flex" :data-testid="props.testId">
      <Icon :name="props.icon" v-if="props.icon" class="mr-2"/>
      <span class="mr-2 font-medium" v-text="props.title"/>
      <Icon name="dropdownClosed" class="ml-auto"/>
    </div>
    <Drawer
      v-if="open"
      :no-space="props.noSpace"
      :scroll="props.scroll"
      :open-to-left="props.openToLeft">
      <slot/>
    </Drawer>
    <div v-if="props.blip" v-text="props.blip" :class="[
      'absolute -top-1 -right-1',
      'rounded-full size-4 leading-4 text-center text-xs',
      'bg-primary text-on-primary',
    ]"/>
  </Material>
</template>

<script setup lang="ts">
import {computed, watch} from 'vue';
import Icon from "../icons/Icon.vue";
import {IconName} from "../icons/icons";
import {useClickOutside} from "../vue/clickOutside";
import {DrawerScroll} from "./Drawer";
import Drawer from "./Drawer.vue";
import Material from "./Material.vue";

interface Props {
  title: string;
  icon?: IconName;
  testId?: string;
  nested?: boolean;
  nestedFlush?: boolean;
  noSpace?: boolean;
  scroll: DrawerScroll;
  blip?: string;
  openToLeft?: boolean;
  hasError?: boolean;
  disabled?: boolean;
}

const props = defineProps<Props>();
const open = defineModel('open', {default: false, type: Boolean});
const clickOutside = useClickOutside(true);

function toggle(): void {
  if (!props.disabled) {
    open.value = !open.value;
  }
}

watch(open, (newValue: boolean): void => {
  if (newValue) {
    clickOutside.addClickListener(() => open.value = false);
  } else {
    clickOutside.removeAll();
  }
});

const borderClass = computed(() => {
  if (props.nested || props.nestedFlush) {
    if (props.hasError) {
      throw new Error('Nested inputs do not have errors.');
    }
    return '';
  }
  if (props.hasError) {
    return 'border border-red-500';
  }
  return 'border border-neutral2-300';
});

const disabledClass = computed(() => {
  if (props.disabled) {
    return 'text-neutral2-400 border border-tile-border';
  }
  return 'cursor-pointer';
});
</script>

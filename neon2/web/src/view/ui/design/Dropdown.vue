<template>
  <Design.Drawer
    :title="title"
    :icon="props.icon"
    :test-id="props.testId"
    :nested="props.nested"
    :openToLeft="props.openToLeft"
    no-space
    v-model:open="open">
    <div
      v-for="option in props.options"
      @click="select(option.value)"
      class="cursor-pointer text-nowrap px-2 py-2 rounded"
      :class="{'text-accent-front bg-accent-back':selected === option.value}">
      <Icon v-if="selected === option.value" name="dropdownOptionSelected" class="text-primary mr-1"/>
      {{option.title}}
    </div>
  </Design.Drawer>
</template>

<script setup lang="ts" generic="U extends string|number">
import {computed, ref} from "vue";
import Icon, {IconName} from "../icons/Icon.vue";
import {Design} from "./design";

export interface DrawerOption<T> {
  value: T;
  title: string;
}

interface Props {
  title?: string;
  icon?: IconName;
  testId?: string;
  nested?: boolean;
  options: DrawerOption<U>[];
  openToLeft?: boolean;
}

const props = defineProps<Props>();
const selected = defineModel<U>({required: true});
const open = ref<boolean>(false);

function select(value: U): void {
  selected.value = value;
  open.value = false;
}

const title = computed((): string => {
  if (props.title) {
    return props.title;
  }
  return props.options.find(option => option.value === selected.value)!.title;
});
</script>

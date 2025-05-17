<template>
  <Design.Drawer
    :title="title"
    :icon="props.icon"
    :test-id="props.testId"
    :nested="props.nested"
    :nested-flush="props.nestedFlush"
    :openToLeft="props.openToLeft"
    :scrollable="props.scrollable"
    :has-error="hasError"
    :disabled="props.disabled"
    no-space
    v-model:open="open">
    <div
      v-for="option in props.options"
      @click="select(option.value)"
      class="cursor-pointer text-nowrap px-2 py-2 rounded"
      :class="{accent: selected === option.value}">
      <Icon v-if="selected === option.value" name="dropdownOptionSelected" class="text-primary mr-1"/>
      {{option.title}}
    </div>
  </Design.Drawer>
  <select class="absolute opacity-0 pointer-events-none" v-model="selected" :id="fieldLabelId">
    <option v-for="option in props.options" :value="option.value" v-text="option.title"/>
  </select>
</template>

<script setup lang="ts" generic="U extends string|number|boolean|null">
import {computed, inject, ref} from "vue";
import Icon from "../icons/Icon.vue";
import {IconName} from "../icons/icons";
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
  nestedFlush?: boolean;
  options: DrawerOption<U>[];
  openToLeft?: boolean;
  default?: string;
  scrollable?: boolean;
  disabled?: boolean;
}

const props = defineProps<Props>();
const selected = defineModel<U>({required: true});
const open = ref<boolean>(false);

const hasError = inject('fieldHasError', computed(() => false));
const fieldLabelId = inject('fieldLabelId', undefined);

function select(value: U): void {
  selected.value = value;
  open.value = false;
}

const title = computed((): string => {
  if (props.title) {
    return props.title;
  }
  return props.options.find(option => option.value === selected.value)?.title || props.default || '';
});
</script>

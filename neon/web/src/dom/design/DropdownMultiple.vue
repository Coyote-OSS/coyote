<template>
  <Design.Drawer
    scrollable
    :nested="nested"
    :test-id="props.testId"
    :title="props.title"
    :icon="props.icon"
    :blip="blip">
    <Design.CheckBox
      v-for="option in props.options"
      :label="option.title"
      :icon="option.icon"
      :model-value="selected(option.value)"
      @update:model-value="newValue => toggle(option.value, newValue)"/>
  </Design.Drawer>
</template>

<script setup lang="ts" generic="T extends string">
import {computed, ModelRef} from "vue";
import {IconName} from "../component/Icon.vue";
import {Design} from "./design";

const model: ModelRef<T[], string, Dupa[], T[]> = defineModel<T[], Dupa[], T[]>({type: Array, required: true});

const props = defineProps<Props>();

interface Props {
  nested?: boolean;
  testId: string;
  title: string;
  icon: IconName;
  options: DropdownOption[];
  blip?: string;
}

export interface DropdownOption {
  value: string;
  title: string;
  icon?: IconName;
}

function selected(value: T): boolean {
  return model.value.includes(value);
}

function toggle(value: T, checked: boolean): void {
  if (checked) {
    model.value.push(value);
  } else {
    model.value.splice(model.value.indexOf(value), 1);
  }
}

const blip = computed((): string|undefined => {
  if (model.value.length === 0) {
    return undefined;
  }
  return model.value.length.toString();
});
</script>

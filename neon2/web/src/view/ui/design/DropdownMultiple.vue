<template>
  <Dropdown
    scroll="scrollablePadding"
    :nested="nested"
    :test-id="props.testId"
    :title="props.title"
    :icon="props.icon"
    :blip="valuesCount"
    :open-to-left="props.openToLeft">
    <CheckBox
      v-for="option in props.options"
      :label="option.title"
      :icon="option.icon"
      :model-value="selected(option.value)"
      @update:model-value="newValue => toggle(option.value, newValue)"/>
  </Dropdown>
</template>

<script setup lang="ts" generic="T extends string">
import {computed} from "vue";
import {IconName} from "../icons/icons";
import CheckBox from "./CheckBox.vue";
import Dropdown from "./Dropdown.vue";
import {DropdownOption} from "./DropdownOption";

const props = defineProps<Props>();
const model = defineModel<T[]>({type: Array, required: true});

interface Props {
  nested?: boolean;
  testId?: string;
  title: string;
  icon: IconName;
  options: DropdownOption<T>[];
  blip?: string;
  openToLeft?: boolean;
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

const valuesCount = computed((): string|undefined => {
  if (model.value.length === 0) {
    return undefined;
  }
  return model.value.length.toString();
});
</script>

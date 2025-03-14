<template>
  <Design.Drawer
    :title="title"
    :icon="props.icon"
    :test-id="props.testId"
    :nested="props.nested"
    no-space
    v-model:open="open">
    <div
      v-for="option in options"
      @click="select(option.value)"
      class="cursor-pointer text-nowrap px-2 py-2 rounded"
      :class="{'text-accent-front bg-accent-back':selected === option.value}">
      <Icon v-if="selected === option.value" name="dropdownOptionSelected" class="text-primary mr-1"/>
      {{ option.title }}
    </div>
  </Design.Drawer>
</template>

<script setup lang="ts">
import {computed, ref} from "vue";
import Icon, {IconName} from "../component/Icon.vue";
import {Design} from "./design";

interface DrawerOption {
  value: string;
  title: string;
}

type DrawerOptions = DrawerOption[]|string[];

interface Props {
  title?: string;
  icon?: IconName;
  testId?: string;
  nested?: boolean;
  options: DrawerOptions;
}

const props = defineProps<Props>();
const selected = defineModel({type: String});
const open = ref<boolean>(false);

function select(value: string): void {
  selected.value = value;
  open.value = false;
}

const title = computed((): string => {
  if (props.title) {
    return props.title;
  }
  if (selected.value) {
    return options.value.find(option => option.value === selected.value).title;
  }
  throw new Error('Without v-model, :title="" is required in <Design.Dropdown/>.');
});

const options = computed<DrawerOption[]>(() => {
  return props.options.map(function (option: DrawerOption|string): DrawerOption {
    if (typeof option === 'string') {
      return {value: option, title: option};
    }
    return option;
  });
});
</script>

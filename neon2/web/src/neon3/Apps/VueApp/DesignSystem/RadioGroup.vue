<template>
  <div :class="props.vertical ? 'space-y-4' : 'flex space-x-8'">
    <RadioButton
      v-for="option in props.options"
      :title="option.title"
      :selected="option.value === selected"
      @select="select(option)"/>
  </div>
</template>

<script setup lang="ts" generic="U extends string|number">
import {DrawerOption} from "./DropdownSingle.vue";
import RadioButton from "./RadioButton.vue";

const props = defineProps<Props>();

interface Props {
  options: DrawerOption<U>[];
  vertical?: boolean;
}

function select(option: DrawerOption<U>): void {
  selected.value = option.value;
}

const selected = defineModel<U, U, U>({required: true});
</script>

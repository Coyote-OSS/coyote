<template>
  <div class="flex space-x-4">
    <PaymentMethodTile
      v-for="option in props.options"
      :title="option.title"
      :selected="option.value === selected"
      @select="select(option)"/>
  </div>
</template>

<script setup lang="ts" generic="U extends string|number">
import {DrawerOption} from "./DropdownSingle.vue";
import PaymentMethodTile from "./PaymentMethodTile.vue";

const props = defineProps<Props>();

interface Props {
  options: DrawerOption<U>[];
}

function select(option: DrawerOption<U>): void {
  selected.value = option.value;
}

const selected = defineModel<U, U, U>();
</script>

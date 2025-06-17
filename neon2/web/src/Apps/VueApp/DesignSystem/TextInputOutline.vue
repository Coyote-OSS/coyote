<template>
  <div class="rounded-lg p-2 flex" :class="inputClass">
    <Icon
      :name="props.icon"
      v-if="props.icon"
      :class="['mr-2', {'text-red-500': hasError}]"/>
    <slot/>
  </div>
</template>

<script setup lang="ts">
import {computed, inject} from "vue";
import Icon from '../Icon/Icon.vue';
import {IconName} from "../Icon/icons";

interface Props {
  icon?: IconName;
  nested?: boolean;
  disabled?: boolean;
}

const props = defineProps<Props>();
const hasError = inject('fieldHasError', computed(() => false));
const disabled = inject('fieldDisabled', computed(() => props.disabled));

const inputClass = computed<string>(() => {
  if (disabled.value) {
    return 'text-neutral2-400 bg-neutral2-025 border border-tile-border';
  }
  if (props.nested) {
    if (hasError.value) {
      throw new Error('Nested inputs do not have errors.');
    }
    return 'bg-tile-nested';
  }
  if (hasError.value) {
    return 'border border-red-500';
  }
  return 'border border-neutral2-300';
});
</script>

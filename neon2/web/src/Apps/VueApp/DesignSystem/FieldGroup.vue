<template>
  <div class="flex-1">
    <label :for="fieldLabelId">
      <FieldLabel
        :has-error="hasError"
        :title="props.label"
        :required="props.required"
        :disabled="props.disabled"/>
    </label>
    <slot/>
    <FieldError v-if="hasError" :message="props.error!"/>
  </div>
</template>

<script setup lang="ts">
import {computed, provide} from "vue";
import FieldError from "./FieldError.vue";
import FieldLabel from "./FieldLabel.vue";

interface Props {
  label: string;
  required?: boolean;
  error?: string|null;
  disabled?: boolean;
}

const props = defineProps<Props>();

const hasError = computed((): boolean => {
  if (props.disabled) {
    return false;
  }
  return !!props.error;
});

const fieldLabelId = Math.random().toString().substring(2);
provide('fieldLabelId', fieldLabelId);
provide('fieldHasError', hasError);
provide('fieldDisabled', computed(() => props.disabled));
</script>

<template>
  <TextInputOutline :disabled="props.disabled" :icon="props.icon" :nested="props.nested">
    <TextInput
      ref="input"
      class="outline-none flex-grow-1"
      :type="props.formatHtml ? 'html' : 'plain'"
      :label-id="fieldLabelId"
      :test-id="props.testId"
      :placeholder="props.placeholder"
      :disabled="disabled"
      v-model="text"/>
    <slot/>
  </TextInputOutline>
</template>

<script setup lang="ts">
import {inject, onMounted, ref} from "vue";
import {IconName} from "../Icon/icons";
import TextInput from "./TextInput.vue";
import TextInputOutline from "./TextInputOutline.vue";

interface Props {
  placeholder: string;
  testId?: string;
  nested?: boolean;
  disabled?: boolean;
  formatHtml?: boolean;
  icon?: IconName;
  gainFocus?: boolean;
}

const text = defineModel<string>({default: ''});
const props = defineProps<Props>();
const fieldLabelId = inject<string|undefined>('fieldLabelId', undefined);

const input = ref<HTMLInputElement>();

onMounted(() => {
  if (props.gainFocus) {
    input.value!.focus({preventScroll: true});
  }
});

function focus(options?: FocusOptions): void {
  input.value!.focus({preventScroll: true});
}

defineExpose({focus});
</script>

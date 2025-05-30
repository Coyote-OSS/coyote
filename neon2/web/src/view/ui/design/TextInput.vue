<template>
  <input
    ref="plainControl"
    v-if="props.type === 'plain'"
    v-model="model"
    :placeholder="props.placeholder"
    :data-testid="props.testId"
    :disabled="props.disabled"
    :id="props.labelId"/>
  <TextFieldFormat
    v-else
    v-model="model"
    :placeholder="props.placeholder"
    :test-id="props.testId"/>
</template>

<script setup lang="ts">
import {ref} from "vue";
import TextFieldFormat from "../external/TextFieldFormat.vue";

const props = defineProps<Props>();
const model = defineModel<string>({required: true});

interface Props {
  type: 'plain'|'html';
  placeholder: string;
  testId?: string;
  disabled?: boolean;
  labelId?: string;
}

const plainControl = ref<HTMLInputElement|null>(null);

function focus(): void {
  plainControl.value!.focus();
}

defineExpose({focus});
</script>

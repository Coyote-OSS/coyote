<template>
  <div class="border p-4 rounded-lg flex items-center bg-blue-050 border-blue-200">
    <Icon name="toastInfo" class="mr-2 text-blue-600"/>
    <p class="font-bold mr-auto"
       :data-testid="props.testId"
       :data-test-value="props.testValue"
       v-text="props.title"
       v-if="props.title"/>
    <p class="text-sm" v-text="props.subtitle" v-if="props.subtitle"/>
    <Icon name="toastClose" v-if="props.closable"/>
  </div>
</template>

<script setup lang="ts">
import Icon from "../Icon/Icon.vue";

const props = defineProps<Props>();

interface Props {
  title?: string;
  subtitle?: string;
  testId?: string;
  testValue?: string;
  closable?: boolean;
}
</script>

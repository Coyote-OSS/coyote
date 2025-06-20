<template>
  <Button
    primary
    class="relative"
    :disabled="props.loading"
    :cursor-wait="props.loading"
    @click="click">
    <Icon
      v-if="props.loading"
      name="buttonLoading"
      class="spin absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"/>
    <span v-text="props.title" :class="{invisible: props.loading}"/>
  </Button>
</template>

<script setup lang="ts">
import Icon from "../Icon/Icon.vue";
import Button from "./Button.vue";

const props = defineProps<Props>();
const emit = defineEmits(['click']);

interface Props {
  title: string;
  loading?: boolean;
}

function click(): void {
  if (!props.loading) {
    emit('click');
  }
}
</script>

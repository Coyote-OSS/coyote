<template>
  <div class="flex items-center cursor-pointer" @click="toggle" ref="element">
    <div
      class="size-5 rounded flex items-center flex-shrink-0 justify-center transition duration-200"
      :class="checked ? 'bg-primary text-on-primary' : 'bg-tile-nested border-1 border-neutral2-200'"
      :data-testid="props.testId">
      <Icon name="checkboxChecked" v-if="checked"/>
    </div>
    <Icon v-if="props.icon" class="ml-2 flex-shrink-0" :name="props.icon"/>
    <label v-if="props.label" class="ml-2 flex-shrink-0 cursor-pointer" v-text="props.label"/>
  </div>
</template>

<script setup lang="ts">
import {ref} from "vue";
import Icon from "../Icon/Icon.vue";
import {IconName} from "../Icon/icons";

interface Props {
  label: string;
  icon?: IconName;
  testId?: string;
}

const props = defineProps<Props>();
const checked = defineModel({default: false});

function toggle(): void {
  checked.value = !checked.value;
}

const element = ref<HTMLElement|null>();

function scrollIntoView(): void {
  element.value!.scrollIntoView({block: "nearest", inline: "nearest"});
}

function label(): string {
  return props.label;
}

defineExpose({scrollIntoView, label});
</script>

<template>
  <div class="flex items-center cursor-pointer" @click="toggle">
    <div
      class="size-5 rounded flex items-center flex-shrink-0 justify-center transition duration-200"
      :class="checked ? 'bg-primary text-on-primary' : 'bg-tile-nested border-1 border-navy-100 dark:border-neutral-700'"
      :data-testid="props.testId">
      <Icon name="checkboxChecked" v-if="checked"/>
    </div>
    <Icon v-if="props.icon" class="ml-2 flex-shrink-0" :name="props.icon"/>
    <label v-if="props.label" class="ml-2 flex-shrink-0 cursor-pointer" v-text="props.label"/>
  </div>
</template>

<script setup lang="ts">
import Icon, {IconName} from "../icons/Icon.vue";

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
</script>

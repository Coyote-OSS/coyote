<template>
  <Design.Tile :nested="props.nested" class="relative">
    <div class="cursor-pointer flex" @click="toggle" :data-testid="props.testId">
      <Icon :name="props.icon" v-if="props.icon" class="mr-2"/>
      <span class="mr-2">{{ props.title }}</span>
      <Icon name="dropdownClosed" class="ml-auto"/>
    </div>
    <Design.Tile
      v-if="open"
      class="absolute z-[1] min-w-full w-max border border-divider"
      :space="!props.noSpace"
      :vertical="!props.noSpace">
      <slot/>
    </Design.Tile>
  </Design.Tile>
</template>

<script setup lang="ts">
import {watch} from 'vue';
import Icon, {IconName} from "../component/Icon.vue";
import {useClickOutside} from "../vue/clickOutside";
import {Design} from "./design";

interface Props {
  title: string;
  icon?: IconName;
  testId?: string;
  nested?: boolean;
  noSpace?: boolean;
}

const props = defineProps<Props>();
const open = defineModel('open', {default: false, type: Boolean});
const clickOutside = useClickOutside(true);

function toggle(): void {
  open.value = !open.value;
}

watch(open, (newValue: boolean): void => {
  if (newValue) {
    clickOutside.addClickListener(() => open.value = false);
  } else {
    clickOutside.removeAll();
  }
});
</script>

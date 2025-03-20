<template>
  <Design.Tile :nested="props.nested" class="cursor-pointer relative" @click="toggle">
    <div class="flex" :data-testid="props.testId">
      <Icon :name="props.icon" v-if="props.icon" class="mr-2"/>
      <span class="mr-2 font-medium" v-text="props.title"/>
      <Icon name="dropdownClosed" class="ml-auto"/>
    </div>
    <Design.Tile @click.stop v-if="open" :space="!props.noSpace" class="absolute z-[1] min-w-full w-max border border-divider">
      <div :class="{'space-y-4':!props.noSpace, 'max-h-96 overflow-y-auto pr-12 -mr-3': props.scrollable}">
        <slot/>
      </div>
    </Design.Tile>
    <div v-if="props.blip" v-text="props.blip" :class="[
      'absolute -top-1 -right-1',
      'rounded-full size-4 leading-4 text-center text-xs',
      'bg-primary text-on-primary',
    ]"/>
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
  scrollable?: boolean;
  blip?: string;
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

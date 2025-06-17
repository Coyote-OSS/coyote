<template>
  <Material
    @click.stop
    :space="!props.noSpace"
    class="absolute top-full z-[1] min-w-full w-max border border-tile-border"
    :class="props.openToLeft ? 'right-0' : 'left-0'">
    <div :class="[{'space-y-4': !props.noSpace}, drawerClass]">
      <slot/>
    </div>
  </Material>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {DrawerScroll} from "./Drawer";
import Material from "./Material.vue";

const props = defineProps<Props>();

interface Props {
  noSpace?: boolean;
  openToLeft?: boolean;
  scroll: DrawerScroll;
}

const drawerClass = computed(() => {
  if (props.scroll === 'scrollable') {
    return 'max-h-96 overflow-y-auto';
  }
  if (props.scroll === 'scrollablePadding') {
    return 'max-h-96 overflow-y-auto pr-3 -mr-3';
  }
  return '';
});
</script>

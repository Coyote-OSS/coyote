<template>
  <ShowcaseGrid v-if="isEmbedded"/>
  <div v-else class="flex flex-wrap items-start justify-center gap-10 bg-gray-50 px-6 py-10">
    <div v-for="device in devices" :key="device.label" class="flex flex-col items-center gap-3">
      <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-500">
        <Icon :name="device.icon" class="text-sm"/>
        {{ device.label }}
      </span>
      <iframe
        :src="frameSrc"
        :width="device.width"
        :height="device.height"
        class="rounded-[2rem] border-8 border-(--palette-gray-800) bg-gray-100 shadow-lg"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed} from 'vue';
import Icon, {type IconName} from '../../libs/Icon/Icon.vue';
import ShowcaseGrid from './ShowcaseGrid.vue';

// Tailwind media queries evaluate against the real browser viewport, not a
// resized container, so the only way to preview the `sm:` breakpoint without
// resizing the actual window is to render the grid in an iframe with a fixed
// width — the iframe gets its own independent viewport for CSS purposes.
const isEmbedded = new URLSearchParams(window.location.search).get('embed') === '1';

const devices: {label: string; icon: IconName; width: number; height: number}[] = [
  {label: 'Desktop', icon: 'viewportDesktop', width: 1024, height: 700},
  {label: 'Mobile', icon: 'viewportMobile', width: 390, height: 844},
];

const frameSrc = computed(() => {
  const url = new URL(window.location.href);
  url.searchParams.set('embed', '1');
  return url.toString();
});
</script>

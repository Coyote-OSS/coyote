<template>
  <div class="flex min-h-screen flex-wrap items-start justify-center gap-10 bg-gray-50 px-6 py-10">
    <div v-for="device in devices" :key="device.label" class="flex flex-col items-center gap-3">
      <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-500">
        <Icon :name="device.icon" class="text-sm"/>
        {{device.label}}
      </span>
      <iframe
        class="rounded-[2rem] border-8 border-(--palette-gray-800) bg-gray-100 shadow-lg"
        :src="frameSrc(device)"
        :width="device.width"
        :height="device.height"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon, {type IconName} from '../../libs/Icon/Icon.vue';

interface Device {
  label: string;
  icon: IconName;
  width: number;
  height: number;
  theme: 'light'|'dark';
}

const devices: Device[] = [
  {label: 'Desktop · Dark', icon: 'viewportDesktop', width: 1200, height: 700, theme: 'dark'},
  {label: 'Desktop · Light', icon: 'viewportDesktop', width: 1200, height: 700, theme: 'light'},
  {label: 'Mobile · Dark', icon: 'viewportMobile', width: 390, height: 844, theme: 'dark'},
  {label: 'Mobile · Light', icon: 'viewportMobile', width: 390, height: 844, theme: 'light'},
];

function frameSrc(device: Device): string {
  const url = new URL(window.location.href);
  url.searchParams.set('embed', '1');
  url.searchParams.set('theme', device.theme);
  return url.toString();
}
</script>

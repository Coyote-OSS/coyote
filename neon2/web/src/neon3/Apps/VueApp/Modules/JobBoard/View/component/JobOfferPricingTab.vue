<template>
  <span class="inline-block p-2 rounded-full bg-tile">
    <span
      v-for="tab in tabs"
      class="inline-block p-3 rounded-full cursor-pointer"
      :class="selected === tab.value ? 'accent' : 'text-neutral2-500'"
      @click="selected = tab.value"
    >
      {{tab.title}}
      <span
        class="rounded-full font-medium text-black bg-[#8fbf8c] ml-1 py-1 px-2"
        v-if="tab.badge"
        v-text="tab.badge"/>
    </span>
  </span>
</template>

<script setup lang="ts">
export type PricingTab = 'offers'|'bundles';

const selected = defineModel<PricingTab>({required: true});

interface Tab {
  value: PricingTab;
  title: string;
  badge?: string;
}

const tabs: Tab[] = [
  {value: 'offers', title: '1 ogłoszenie'},
  {value: 'bundles', title: 'Pakiety', badge: '-50%'},
];
</script>

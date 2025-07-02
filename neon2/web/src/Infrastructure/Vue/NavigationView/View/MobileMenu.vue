<template>
  <div class="absolute top-1 pt-16.5 w-full z-[1] h-dvh">
    <div class="px-4 py-7 bg-tile text-neutral2-600 flex flex-col border-t border-tile-border h-full">
      <div :class="navigationLinkStyle" @click="service.showJobOffers(); close();" v-text="'Dla kandydatów'"/>
      <div :class="navigationLinkStyle" @click="service.showPricing(); close();" v-text="'Dla pracodawców'"/>
      <div :class="navigationLinkStyle" @click="service.showForum(); close();" v-text="'Forum'"/>
      <div :class="navigationLinkStyle" @click="service.showBlog(); close();" v-text="'Mikroblogi'"/>
      <Design.Button primary class="mt-auto w-full" @click="service.showRegister(); close();" title="Zarejestruj się"/>
      <Design.Button primary-outline class="mt-3 w-full" @click="service.showLogin(); close();" title="Zaloguj"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {Design} from "../../DesignSystem/design";
import {useNavigationService} from "./vue";

const service = useNavigationService();
const navigationLinkStyle = 'py-3 text-xl active:text-green2-500';
const emit = defineEmits(['close']);

function close(): void {
  emit('close');
}
</script>

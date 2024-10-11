<template>
  <nav class="flex items-center gap-3 py-3 text-neutral2-500">
    <img :src="logo" alt="4programmers.net" class="h-6 -mt-1"/>
    <div class="cursor-pointer" @click="showJobOffers">
      Praca
    </div>
    <div class="group">
      <div>
        <a href="/Forum" class="mr-1">Forum</a>
        <Icon name="dropdownClosed"/>
      </div>
      <div class="relative z-[1] hidden group-hover:block">
        <ForumMenu class="absolute top-0 left-0"/>
      </div>
    </div>
    <div class="cursor-pointer" @click="showPricing">
      Dla pracodawców
    </div>
  </nav>
</template>

<script setup lang="ts">
import {inject} from "vue";
import logo from "../../../../../../../../public/img/logo-footer-modern-light.svg";
import Icon from "../../Icon/Icon.vue";
import ForumMenu from "./ForumMenu.vue";
import {NavigationService} from "./NavigationService";
import {useNavigationStore} from "./store";
import {navigationServiceInjectKey} from "./View/vue";

const store = useNavigationStore();
const service = inject<NavigationService>(navigationServiceInjectKey)!;

function showPricing(): void {
  service.showPricing();
}

function showJobOffers(): void {
  service.showJobOffers();
}
</script>

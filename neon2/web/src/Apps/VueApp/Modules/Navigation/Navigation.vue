<template>
  <nav class=" text-neutral2-500 bg-tile text-lg">
    <div class="h-17.5 p-3 gap-x-4 flex items-center mx-auto max-w-400">
      <a href="/public">
        <img :src="logo" alt="4programmers.net" class="w-45"/>
      </a>
      <div class="mr-auto"/>
      <div class="cursor-pointer py-2 px-3 rounded hover:accent" @click="showJobOffers">
        Dla kandydatów
      </div>
      <div class="cursor-pointer py-2 px-3 rounded hover:accent" @click="showPricing">
        Dla pracodawców
      </div>
      <div class="group/navItem">
        <div class="cursor-pointer py-2 px-3 rounded group-hover/navItem:accent">
          Forum
        </div>
        <div class="relative z-[1]">
          <ForumMenu/>
        </div>
      </div>
      <div class="mr-auto"/>
      <Button primary-outline class="text-base h-10 hover:accent" @click="showPricing">
        Dodaj ofertę pracy
      </Button>
      <UserAvatar :authenticated="store.isAuthenticated"/>
    </div>
  </nav>
</template>

<script setup lang="ts">
import {inject} from "vue";
import logo from "../../../../../../../public/img/logo.light.svg";
import Button from "../../DesignSystem/Button.vue";
import ForumMenu from "./ForumMenu.vue";
import {NavigationService} from "./NavigationService";
import {useNavigationStore} from "./store";
import UserAvatar from "./View/UserAvatar.vue";
import {navigationServiceInjectKey} from "./View/vue";

const service = inject<NavigationService>(navigationServiceInjectKey)!;
const store = useNavigationStore();

function showPricing(): void {
  service.showPricing();
}

function showJobOffers(): void {
  service.showJobOffers();
}
</script>

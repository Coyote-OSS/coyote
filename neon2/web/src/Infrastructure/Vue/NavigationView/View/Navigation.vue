<template>
  <nav class=" text-neutral2-600 bg-tile text-lg">
    <div class="h-17.5 p-3 gap-x-4 flex items-center mx-auto max-w-400">
      <a href="/">
        <BrandLogo/>
      </a>
      <div class="cursor-pointer py-2 px-3 rounded hover:text-green2-500" @click="showJobOffers">
        Dla kandydatów
      </div>
      <div class="cursor-pointer py-2 px-3 rounded hover:text-green2-500" @click="showPricing">
        Dla pracodawców
      </div>
      <div class="group/navItem">
        <div class="cursor-pointer py-2 px-3 rounded group-hover/navItem:text-green2-500">
          Forum
        </div>
        <div class="relative z-[1]">
          <ForumMenu/>
        </div>
      </div>
      <div class="mr-auto"/>
      <Button primary-outline class="text-base h-10" @click="showPricing">
        Dodaj ofertę pracy
      </Button>
      <div class="cursor-pointer relative hover:text-green2-500"
           @click="showNotifications"
           v-if="store.isAuthenticated">
        <Icon name="navigationNotification" class="size-10 text-xl text-center leading-10"/>
        <Blip v-if="notifications > 0" :value="notifications" important/>
      </div>
      <UserAvatar :user="store.navigationUser"/>
    </div>
  </nav>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Blip from "../../DesignSystem/Blip.vue";
import Button from "../../DesignSystem/Button.vue";
import Icon from "../../Icon/Icon.vue";
import {useNavigationStore} from "../navigationStore";
import BrandLogo from "./BrandLogo.vue";
import ForumMenu from "./ForumMenu.vue";
import UserAvatar from "./UserAvatar.vue";
import {useNavigationService} from "./vue";

const service = useNavigationService();
const store = useNavigationStore();

function showPricing(): void {
  service.showPricing();
}

function showJobOffers(): void {
  service.showJobOffers();
}

function showNotifications(): void {
  service.attemptNotifications();
}

const notifications = computed(() => store.navigationUser?.notificationsCount || 0);
</script>

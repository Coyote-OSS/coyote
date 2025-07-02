<template>
  <div class="relative">
    <nav class="text-neutral2-600 bg-tile text-lg relative z-[2]">
      <div class="h-17.5 p-3 gap-x-4 flex items-center mx-auto max-w-400">
        <a href="/">
          <BrandLogo/>
        </a>
        <div :class="navigationLinkStyle" @click="service.showJobOffers()">
          Dla kandydatów
        </div>
        <div :class="navigationLinkStyle" @click="service.showPricing()">
          Dla pracodawców
        </div>
        <div class="max-lg:hidden group/navItem">
          <div class="cursor-pointer py-2 px-3 rounded group-hover/navItem:text-green2-500"
               @click="service.showForum()">
            Forum
          </div>
          <div class="relative z-[1]">
            <ForumMenu/>
          </div>
        </div>
        <div :class="navigationLinkStyle" @click="service.showBlog()">
          Mikroblogi
        </div>
        <Button class="max-lg:hidden text-base h-10 ml-auto" primary-outline @click="service.showPricing()">
          Dodaj ofertę pracy
        </Button>
        <div class="max-lg:hidden cursor-pointer relative hover:text-green2-500"
             @click="service.attemptNotifications()"
             v-if="store.isAuthenticated">
          <Icon name="navigationNotification" class="size-10 text-xl text-center leading-10"/>
          <Blip v-if="notifications > 0" :value="notifications" important/>
        </div>
        <UserAvatar class="max-lg:hidden" :user="store.navigationUser"/>
        <div class="lg:hidden ml-auto mr-4 cursor-pointer">
          <Icon :name="mobileMenuIcon" @click="toggleMobileMenu" class="text-xl"/>
        </div>
      </div>
    </nav>
    <MobileMenu v-if="mobileMenuOpen" @close="closeMobileMenu"/>
  </div>
</template>

<script setup lang="ts">
import {computed, ref} from "vue";
import Blip from "../../DesignSystem/Blip.vue";
import Button from "../../DesignSystem/Button.vue";
import Icon from "../../Icon/Icon.vue";
import {IconName} from "../../Icon/icons";
import {useNavigationStore} from "../navigationStore";
import BrandLogo from "./Component/BrandLogo.vue";
import ForumMenu from "./ForumMenu.vue";
import MobileMenu from "./MobileMenu.vue";
import UserAvatar from "./UserAvatar.vue";
import {useNavigationService} from "./vue";

const service = useNavigationService();
const store = useNavigationStore();
const mobileMenuOpen = ref<boolean>(false);

const navigationLinkStyle = 'max-lg:hidden cursor-pointer py-2 px-3 rounded hover:text-green2-500';

function toggleMobileMenu(): void {
  mobileMenuOpen.value = !mobileMenuOpen.value;
  blockScroll(mobileMenuOpen.value);
}

function closeMobileMenu(): void {
  mobileMenuOpen.value = false;
  blockScroll(false);
}

function blockScroll(blocked: boolean): void {
  if (blocked) {
    document.body.classList.add('overflow-y-hidden');
  } else {
    document.body.classList.remove('overflow-y-hidden');
  }
}

const notifications = computed(() => store.navigationUser?.notificationsCount || 0);
const mobileMenuIcon = computed<IconName>(() => mobileMenuOpen.value ? 'mobileMenuClose' : 'mobileMenuOpen');
</script>

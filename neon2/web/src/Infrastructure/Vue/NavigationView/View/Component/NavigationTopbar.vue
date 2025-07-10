<template>
  <div class="relative">
    <MobileDrawer v-if="mobileMenuOpen" @close="closeMobileMenu" class="lg:hidden"/>
    <nav class="text-neutral2-600 bg-tile text-lg relative z-[2]">
      <div class="h-17.5 p-3 pl-4 gap-x-4 flex items-center mx-auto max-w-400">
        <BrandLogo/>
        <NavTopbarListItem
          v-for="item in entryPointItems"
          :type="item.type"
          :title="item.title"
          :forum-menu="item.forumMenu"
          @click="action(item.action)"/>
        <NavTopbarListItem type="space"/>
        <NavTopbarListItem type="buttonSecondary" class="max-lg:hidden" title="Dodaj ofertę pracy" @click="action('pricing')"/>
        <div class="max-lg:hidden cursor-pointer relative hover:text-green2-500"
             @click="service.attemptNotifications()"
             v-if="store.isAuthenticated">
          <Icon name="navigationNotification" class="size-10 text-xl text-center leading-10"/>
          <Blip v-if="notifications > 0" :value="notifications" important/>
        </div>
        <DesktopAuthControl class="max-lg:hidden" :user="store.navigationUser"/>
        <Icon :name="mobileMenuIcon" @click="toggleMobileMenu" class="text-xl ml-auto mr-4 lg:hidden"/>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import {computed, ref} from "vue";
import Blip from "../../../DesignSystem/Blip.vue";
import Icon from "../../../Icon/Icon.vue";
import {IconName} from "../../../Icon/icons";
import {NavigationAction} from "../../NavigationService";
import {useNavigationStore} from "../navigationStore";
import {entryPointItems} from "../Presenter/entryPointItems";
import {useNavigationService} from "../vue";
import BrandLogo from "./BrandLogo.vue";
import DesktopAuthControl from "./DesktopAuthControl.vue";
import NavTopbarListItem from "./ListItem/NavTopbarListItem.vue";
import MobileDrawer from "./MobileDrawer.vue";

const service = useNavigationService();
const store = useNavigationStore();
const mobileMenuOpen = ref<boolean>(false);

function toggleMobileMenu(): void {
  mobileMenuOpen.value = !mobileMenuOpen.value;
  if (mobileMenuOpen.value) {
    store.mainContentSuspend();
  } else {
    store.mainContentRestore();
  }
}

function closeMobileMenu(): void {
  mobileMenuOpen.value = false;
  store.mainContentRestore();
}

const notifications = computed(() => store.navigationUser?.notificationsCount || 0);
const mobileMenuIcon = computed<IconName>(() => mobileMenuOpen.value ? 'mobileMenuClose' : 'mobileMenuOpen');

function action(action: NavigationAction): void {
  service.action(action);
}
</script>

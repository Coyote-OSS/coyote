<template>
  <div class="relative">
    <MobileDrawer v-if="mobileMenuOpen" @close="closeMobileMenu" class="lg:hidden"/>
    <div class="text-neutral2-600 bg-tile text-lg relative z-[2] lg:shadow">
      <div class="h-17.5 p-3 pl-4 gap-x-4 flex items-center mx-auto max-w-400">
        <BrandLogo/>
        <NavTopbarListItem
          v-for="item in entryPointItems"
          :type="item.type"
          :title="item.title"
          :forum-menu="item.forumMenu"
          @click="action(item.action)"/>
        <NavTopbarListItem type="space"/>
        <div class="ml-auto"/>
        <NavTopbarListItem type="buttonSecondary" class="max-lg:hidden" title="Dodaj ofertę pracy" @click="action('pricing')"/>
        <NotificationControl v-if="store.isAuthenticated" :user="store.navigationUser" @mark-all="markAll" @load-more="loadMore"/>
        <DesktopAuthControl class="max-lg:hidden" :user="store.navigationUser"/>
        <Icon monospace :name="mobileMenuIcon" @click="toggleMobileMenu" class="text-xl mr-4 lg:hidden"/>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, ref} from "vue";
import Icon from "../../../Icon/Icon.vue";
import {IconName} from "../../../Icon/icons";
import {NavigationAction} from "../../Port/NavigationService";
import {useNavigationStore} from "../navigationStore";
import {entryPointItems} from "../Presenter/entryPointItems";
import {useNavigationService} from "../vue";
import BrandLogo from "./BrandLogo.vue";
import DesktopAuthControl from "./DesktopAuthControl.vue";
import NavTopbarListItem from "./ListItem/NavTopbarListItem.vue";
import MobileDrawer from "./MobileDrawer.vue";
import NotificationControl from "./NotificationControl.vue";

const service = useNavigationService();
const store = useNavigationStore();
const mobileMenuOpen = ref<boolean>(false);

function toggleMobileMenu(): void {
  mobileMenuOpen.value = !mobileMenuOpen.value;
  service.mainContentSuspended(mobileMenuOpen.value);
}

function closeMobileMenu(): void {
  mobileMenuOpen.value = false;
  service.mainContentSuspended(false);
}

const mobileMenuIcon = computed<IconName>(() => mobileMenuOpen.value ? 'mobileMenuClose' : 'mobileMenuOpen');

function action(action: NavigationAction): void {
  service.action(action);
}

function markAll(): void {
  service.markAllNotificationsAsViewed();
}

function loadMore(): void {
  service.loadMoreNotifications();
}
</script>

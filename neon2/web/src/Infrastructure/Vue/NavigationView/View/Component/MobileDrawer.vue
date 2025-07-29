<template>
  <div class="w-full h-dvh flex flex-col bg-tile text-neutral2-600 absolute top-0 z-[1]">
    <div class="h-13.5 flex-shrink-0"/>
    <div class="pt-3 px-4 pb-7 flex flex-col grow border-t border-tile-border overflow-y-scroll">
      <MobileMenuListItem
        v-for="item in entryPointItems"
        :type="item.type"
        :title="item.title"
        @click="action(item.action)"/>
      <MobileMenuListItem
        v-for="item in _authControlItems"
        :type="item.type"
        :title="item.title"
        :icon="item.icon"
        :messages-count="item.messagesCount"
        @click="item.action && action(item.action)"/>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed} from "vue";
import {NavigationAction} from "../../Port/NavigationService";
import {useNavigationStore} from "../navigationStore";
import {authControlItems} from "../Presenter/authControlItems";
import {entryPointItems} from "../Presenter/entryPointItems";
import {useNavigationService} from "../vue";
import MobileMenuListItem from "./ListItem/MobileMenuListItem.vue";

const service = useNavigationService();
const store = useNavigationStore();
const emit = defineEmits(['close']);

function action(action: NavigationAction): void {
  service.action(action);
  if (action !== 'toggleTheme') {
    close();
  }
}

const _authControlItems = computed(() => authControlItems(
  store.$state.navigationUser,
  store.$state.darkTheme,
));

function close(): void {
  emit('close');
}
</script>

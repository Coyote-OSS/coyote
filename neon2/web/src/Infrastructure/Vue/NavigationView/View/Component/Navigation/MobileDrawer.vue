<template>
  <div class="absolute top-1 pt-16.5 w-full z-[1] h-dvh">
    <div class="px-4 pt-3 pb-7 bg-tile text-neutral2-600 flex flex-col border-t border-tile-border h-full">
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
import {NavigationAction} from "../../../NavigationService";
import {useNavigationStore} from "../../navigationStore";
import {authControlItems} from "../../Presenter/authControlItems";
import {entryPointItems} from "../../Presenter/entryPointItems";
import {useNavigationService} from "../../vue";
import MobileMenuListItem from "./ListItem/MobileMenuListItem.vue";

const service = useNavigationService();
const store = useNavigationStore();
const emit = defineEmits(['close']);

function action(action: NavigationAction): void {
  service.action(action);
  emit('close');
}

const _authControlItems = computed(() => authControlItems(store.$state.navigationUser));
</script>

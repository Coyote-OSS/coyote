<template>
  <div
    class="w-full h-dvh flex flex-col bg-tile text-neutral2-600"
    :class="['absolute top-0', nestedDrawerOpen ? 'z-[3]' : 'z-[1]']">
    <div class="h-17.5 p-3 pl-4 flex items-center" v-if="nestedDrawerOpen">
      <span @click="nestedClose" class="text-green2-500 font-medium text-lg">
        <Icon name="mobileMenuBack" class="mr-2"/>
        Powrót
      </span>
      <Icon name="mobileMenuClose" @click="close" class="text-xl  ml-auto mr-4"/>
    </div>
    <div class="h-17.5" v-else/>
    <div class="pt-3 px-4 pb-7 flex flex-col grow border-t border-tile-border">
      <template v-if="nestedDrawerOpen">
        Tutaj linki zagnieżdżone
      </template>
      <template v-else>
        <MobileMenuListItem
          v-for="item in entryPointItems"
          :type="item.type"
          :title="item.title"
          @click="action(item.action)"/>
        <MobileMenuListItem
          type="link"
          title="Show more"
          icon="navigationNavigateIn"
          @click="nestedOpen"/>
        <MobileMenuListItem
          v-for="item in _authControlItems"
          :type="item.type"
          :title="item.title"
          :icon="item.icon"
          :messages-count="item.messagesCount"
          @click="item.action && action(item.action)"/>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, ref} from "vue";
import Icon from "../../../../Icon/Icon.vue";
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
  close();
}

const _authControlItems = computed(() => authControlItems(
  store.$state.navigationUser,
  store.$state.darkTheme,
));

const nestedDrawerOpen = ref<boolean>(false);

function nestedOpen(): void {
  nestedDrawerOpen.value = true;
}

function nestedClose(): void {
  nestedDrawerOpen.value = false;
}

function close(): void {
  emit('close');
}
</script>

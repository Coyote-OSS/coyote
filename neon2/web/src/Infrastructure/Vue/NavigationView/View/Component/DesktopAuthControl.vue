<template>
  <div>
    <span
      class="hidden"
      data-testid="authenticationState"
      :data-test-value="props.user ? 'loggedIn' : 'guest'"/>
    <div class="cursor-pointer relative" data-testid="authControl">
      <UserAvatar :user="props.user" @click="toggleControl"/>
      <div class="relative cursor-default">
        <div
          class="absolute right-0 top-2 bg-tile rounded-lg text-neutral2-500 border border-tile-border shadow-md px-4 py-2"
          v-if="controlOpen">
          <DesktopMenuListItem
            v-for="listItem in _authControlItems"
            :type="listItem.type"
            :title="listItem.title"
            :icon="listItem.icon"
            :messages-count="listItem.messagesCount"
            :action="listItem.action"
            @action="action => service.action(action)"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from "vue";
import {NavigationUser} from "../../../../../Domain/Navigation/NavigationUser";
import {useClickOutside} from "../../../Helper/clickOutside";
import {useNavigationStore} from "../navigationStore";
import {authControlItems} from "../Presenter/authControlItems";
import {useNavigationService} from "../vue";
import DesktopMenuListItem from "./ListItem/DesktopMenuListItem.vue";
import UserAvatar from "./UserAvatar.vue";

const store = useNavigationStore();

const props = defineProps<Props>();

interface Props {
  user: NavigationUser|null;
}

const controlOpen = ref<boolean>(false);

function toggleControl(): void {
  controlOpen.value = !controlOpen.value;
}

function closeControl(): void {
  controlOpen.value = false;
}

const clickOutside = useClickOutside(false);

watch(controlOpen, (newValue: boolean): void => {
  if (newValue) {
    clickOutside.addClickListener(closeControl);
  } else {
    clickOutside.removeAll();
  }
});

const service = useNavigationService();

const _authControlItems = computed(() => authControlItems(
  props.user,
  store.$state.darkTheme,
));
</script>

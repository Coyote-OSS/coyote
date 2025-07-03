<template>
  <div>
    <span
      class="hidden"
      data-testid="authenticationState"
      :data-test-value="props.user ? 'loggedIn' : 'guest'"/>
    <div @click="toggleControl" class="cursor-pointer relative" data-testid="authControl">
      <Blip v-if="hasMessage" :value="props.user!.messagesCount" important/>
      <UserAvatar :user="props.user"/>
      <div class="relative cursor-default">
        <div class="absolute right-0 top-1" v-if="controlOpen">
          <Design.Material space class="text-neutral2-500 border border-tile-border shadow-md py-2">
            <DesktopMenuListItem
              v-for="listItem in _authControlItems"
              :type="listItem.type"
              :title="listItem.title"
              :icon="listItem.icon"
              :messages-count="listItem.messagesCount"
              @click="listItem.action && service.action(listItem.action)"/>
          </Design.Material>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from "vue";
import {NavigationUser} from "../../../../../../Domain/Navigation/NavigationUser";
import Blip from "../../../../DesignSystem/Blip.vue";
import {Design} from "../../../../DesignSystem/design";
import {useClickOutside} from "../../../../Helper/clickOutside";
import {useNavigationStore} from "../../navigationStore";
import {authControlItems} from "../../Presenter/authControlItems";
import {useNavigationService} from "../../vue";
import UserAvatar from "../UserAvatar.vue";
import DesktopMenuListItem from "./ListItem/DesktopMenuListItem.vue";

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

const hasMessage = computed(() => !!props.user && props.user.messagesCount > 0);
const _authControlItems = computed(() => authControlItems(
  props.user,
  store.$state.darkTheme,
));
</script>

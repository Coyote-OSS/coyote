<template>
  <div v-if="store.isAuthenticated">
    <div class="relative hover:text-green2-500 px-2 md:cursor-pointer" @click="toggleNotifications">
      <Icon name="navigationNotification" class="size-10 text-xl text-center leading-10"/>
      <Blip
        v-if="props.user.notificationsCount > 0"
        :value="props.user.notificationsCount"
        important/>
    </div>
    <div class="md:relative text-base" v-if="notificationsOpen">
      <div class="absolute max-md:right-0 max-md:left-0 -right-6 top-17.5 md:top-2">
        <NotificationBox
          :notifications="props.user.notifications"
          class="md:w-132"
          @mark-all="markAll"
          @load-more="loadMore"/>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, watch} from "vue";
import {NavigationUser} from "../../../../../Domain/Navigation/NavigationUser";
import Blip from "../../../DesignSystem/Blip.vue";
import {useClickOutside} from "../../../Helper/clickOutside";
import Icon from "../../../Icon/Icon.vue";
import {useNavigationStore} from "../navigationStore";
import NotificationBox from "./NotificationBox.vue";

const props = defineProps<Props>();
const emit = defineEmits(['markAll', 'loadMore']);

interface Props {
  user: NavigationUser;
}

const store = useNavigationStore();
const notificationsOpen = ref<boolean>(false);

function toggleNotifications(): void {
  notificationsOpen.value = !notificationsOpen.value;
}

function markAll(): void {
  emit('markAll');
}

function loadMore(): void {
  emit('loadMore');
}

function closeNotifications(): void {
  notificationsOpen.value = false;
}

const clickOutside = useClickOutside(false);

watch(notificationsOpen, (newValue: boolean): void => {
  if (newValue) {
    clickOutside.addClickListener(closeNotifications);
  } else {
    clickOutside.removeAll();
  }
});
</script>

<template>
  <div v-if="store.isAuthenticated">
    <div class="flex-shrink-0 flex max-lg:mr-2">
      <NavigationControlItem icon="navigationNotification" :number="props.user.notificationsCount" @action="toggleNotifications"/>
      <NavigationControlItem icon="navigationMessages" :number="props.user.messagesCount" @action="showMessages" class="lg:hidden"/>
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
import {useClickOutside} from "../../../Helper/clickOutside";
import {useNavigationStore} from "../navigationStore";
import {useNavigationService} from "../vue";
import NavigationControlItem from "./NavigationControlItem.vue";
import NotificationBox from "./NotificationBox.vue";

const props = defineProps<Props>();
const emit = defineEmits(['markAll', 'loadMore']);

interface Props {
  user: NavigationUser;
}

const store = useNavigationStore();
const service = useNavigationService();
const notificationsOpen = ref<boolean>(false);

function toggleNotifications(): void {
  notificationsOpen.value = !notificationsOpen.value;
}

function showMessages(): void {
  service.action('messages');
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

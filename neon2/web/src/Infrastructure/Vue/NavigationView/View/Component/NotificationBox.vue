<template>
  <div class="bg-tile text-neutral2-500 px-2 md:px-4 pt-4 border-t md:border border-tile-border md:rounded-lg">
    <div class="flex justify-between font-medium text-sm">
      <a href="/User/Notifications" class="text-green2-500">
        Powiadomienia
        <Icon name="notificationSeeAll"/>
      </a>
      <span class="md:cursor-pointer" v-text="'Oznacz jako przeczytane'" @click="markAllAsViewed"/>
    </div>
    <hr class="text-tile-border mt-3">
    <div class="h-130 overflow-y-scroll" @scroll.passive="scroll">
      <div class="pt-1 pb-3">
        <div v-for="notification in props.notifications" class="py-2.5 mb-1 flex">
          <a class="size-10 rounded user-avatar flex-shrink-0 mt-0.5" :href="notification.actorProfileHref">
            <img :src="notification.actorAvatarUrl" v-if="notification.actorAvatarUrl" class="rounded"/>
            <div v-text="notification.actorInitials" v-else class="text-center leading-10"/>
          </a>
          <a class="ml-2 min-w-0 flex-grow-1"
             :class="{'text-neutral2-400':!notification.notificationHighlighted}"
             :href="notification.contentHref">
            <div class="xs:flex xs:justify-between text-sm">
              <div class="flex items-center order-1 xs:order-3">
                {{notification.notificationDate}}
                <div
                  class="overflow-hidden transition-[width]"
                  :class="notification.notificationHighlighted ? 'w-4' : 'w-0'">
                  <div class="size-2 rounded-full bg-green2-500 ml-2"/>
                </div>
              </div>
              <div v-text="notification.notificationTitle" class="order-2"/>
            </div>
            <div v-text="notification.contentTitle" class="truncate font-medium text-green2-500"/>
            <div v-text="notification.contentPreview" class="truncate text-sm"/>
          </a>
        </div>
        <div v-if="props.notifications.length === 0" class="text-center py-8">
          Nie masz żadnych powiadomień.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {onMounted} from "vue";
import {Notification} from "../../../../../Domain/Navigation/Notification";
import Icon from "../../../Icon/Icon.vue";

const props = defineProps<Props>();
const emit = defineEmits(['markAll', 'loadMore']);

interface Props {
  notifications: Notification[];
}

onMounted(() => {
  if (props.notifications.length === 0) {
    loadMore();
  }
});

function markAllAsViewed(): void {
  emit('markAll');
}

function loadMore(): void {
  emit('loadMore');
}

function scroll(event: Event): void {
  if (scrolledToBottom(event)) loadMore();
}

function scrolledToBottom(event: Event): boolean {
  const element: HTMLElement = event.target;
  const distanceToBottom = Math.abs(element.scrollHeight - element.scrollTop - element.clientHeight);
  return distanceToBottom < 1;
}
</script>

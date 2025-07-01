<template>
  <div>
    <span
      class="hidden"
      data-testid="authenticationState"
      :data-test-value="props.user ? 'loggedIn' : 'guest'"/>
    <div @click="toggleControl" class="cursor-pointer relative" data-testid="authControl">
      <Blip v-if="hasMessage" :value="props.user!.messagesCount" important/>
      <div class="size-10 rounded user-avatar" v-if="props.user">
        <img :src="props.user.avatarUrl" v-if="props.user.avatarUrl" class="rounded"/>
        <div v-text="props.user.avatarInitials" v-else class="text-center leading-10"/>
      </div>
      <Icon
        v-else
        name="navigationGuestAvatar"
        class="cursor-pointer py-3 px-3 rounded hover:accent"/>
      <div class="relative cursor-default">
        <div class="absolute right-0 top-1" v-if="controlOpen">
          <UserControl
            v-if="props.user"
            :username="props.user.username"
            :has-message="hasMessage"
            :messages-count="props.user!.messagesCount"/>
          <GuestControl v-else/>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {computed, ref, watch} from "vue";
import {NavigationUser} from "../../../../Domain/Navigation/NavigationUser";
import Blip from "../../DesignSystem/Blip.vue";
import {useClickOutside} from "../../Helper/clickOutside";
import Icon from "../../Icon/Icon.vue";
import GuestControl from "./GuestControl.vue";
import UserControl from "./UserControl.vue";

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

const hasMessage = computed(() => !!props.user && props.user.messagesCount > 0);
</script>

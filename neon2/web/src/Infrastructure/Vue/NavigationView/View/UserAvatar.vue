<template>
  <div>
    <span
      class="hidden"
      data-testid="authenticationState"
      :data-test-value="props.user ? 'loggedIn' : 'guest'"/>
    <div @click="toggleControl" class="cursor-pointer" data-testid="authControl">
      <div class=" border border-green2-200 rounded bg-green-050" v-if="props.user">
        <img :src="userAvatar" class="size-10 rounded "/>
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
            :has-message="props.user.messagesCount > 0"
            :messages-count="props.user.messagesCount"/>
          <GuestControl v-else/>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, watch} from "vue";

import {NavigationUser} from "../../../../Domain/Navigation/NavigationUser";
import Icon from "../../Icon/Icon.vue";
import {useClickOutside} from "../../Helper/clickOutside";
import userAvatar from "./avatar.png";
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
</script>

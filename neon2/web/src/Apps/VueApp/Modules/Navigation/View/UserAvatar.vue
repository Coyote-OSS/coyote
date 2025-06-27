<template>
  <span
    class="hidden"
    data-testid="authenticationState"
    :data-test-value="props.authenticated ? 'loggedIn' : 'guest'"/>
  <div @click="toggleControl" class="cursor-pointer">
    <div class=" border border-green2-200 rounded bg-green-050" v-if="props.authenticated">
      <img :src="userAvatar" class="size-10 rounded "/>
    </div>
    <Icon
      v-else
      name="navigationGuestAvatar"
      class="cursor-pointer py-3 px-3 rounded hover:accent"/>
    <div class="relative cursor-default">
      <div class="absolute right-0 top-1" v-if="controlOpen">
        <UserControl username="Marek" :has-message="true" :messages-count="3" v-if="props.authenticated"/>
        <GuestControl v-else/>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref} from "vue";
import Icon from "../../../Icon/Icon.vue";
import userAvatar from "./avatar.png";
import GuestControl from "./GuestControl.vue";
import UserControl from "./UserControl.vue";

const props = defineProps<Props>();

interface Props {
  authenticated: boolean;
}

const controlOpen = ref<boolean>(false);

function toggleControl(): void {
  controlOpen.value = !controlOpen.value;
}
</script>

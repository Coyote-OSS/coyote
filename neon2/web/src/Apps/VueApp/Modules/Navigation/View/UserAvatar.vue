<template>
  <span
    class="hidden"
    data-testid="authenticationState"
    :data-test-value="props.authenticated ? 'loggedIn' : 'guest'"/>
  <img v-if="props.authenticated" :src="userAvatar" class="size-10 rounded overflow-hidden"/>
  <Icon
    v-else
    name="navigationUser"
    class="cursor-pointer py-3 px-3 rounded hover:accent"/>
</template>
<script setup lang="ts">
import Icon from "../../../Icon/Icon.vue";
import userAvatar from "./avatar.png";

const props = defineProps<Props>();

interface Props {
  authenticated: boolean;
}
</script>

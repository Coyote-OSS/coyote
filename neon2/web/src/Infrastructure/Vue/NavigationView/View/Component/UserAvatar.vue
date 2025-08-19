<template>
  <div class="size-10 rounded user-avatar" v-if="props.user">
    <img class="rounded size-10 object-cover" :src="props.user.avatarUrl" v-if="props.user.avatarUrl"/>
    <div v-text="props.user.avatarInitials" v-else class="text-center leading-10"/>
  </div>
  <Icon
    v-else
    name="navigationGuestAvatar"
    class="cursor-pointer size-10 leading-10 text-center rounded hover:accent"/>
</template>

<script setup lang="ts">
import {NavigationUser} from "../../../../../Domain/Navigation/NavigationUser";
import Icon from "../../../Icon/Icon.vue";

const props = defineProps<Props>();

interface Props {
  user: NavigationUser|null;
}
</script>

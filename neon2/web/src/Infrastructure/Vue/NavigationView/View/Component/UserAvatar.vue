<template>
  <div class="size-10 rounded user-avatar" v-if="props.user">
    <img :src="props.user.avatarUrl" v-if="props.user.avatarUrl" class="rounded"/>
    <div v-text="props.user.avatarInitials" v-else class="text-center leading-10"/>
  </div>
  <Icon
    v-else
    name="navigationGuestAvatar"
    class="cursor-pointer py-3 px-3 rounded hover:accent"/>
</template>

<script setup lang="ts">
import {NavigationUser} from "../../../../../Domain/Navigation/NavigationUser";
import Icon from "../../../Icon/Icon.vue";

const props = defineProps<Props>();

interface Props {
  user: NavigationUser|null;
}
</script>

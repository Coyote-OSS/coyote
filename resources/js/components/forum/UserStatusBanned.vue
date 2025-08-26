<template>
  <span
    v-if="props.user.is_blocked"
    class="ms-2 user-status user-status--banned text-nowrap"
    :title="title">
    <Icon name="forumUserBannedTemporarily" v-if="!props.user.is_blocked_perm"/>
    Zbanowany
  </span>
</template>

<script setup lang="ts">
import {computed} from "vue";
import Icon from "../../../../neon2/web/src/Infrastructure/Vue/Icon/Icon.vue";
import {User} from "../../types/models";

const props = defineProps<Props>();

interface Props {
  user: User;
}

const title = computed(() => {
  if (props.user.is_blocked_perm) {
    return 'Konto użytkownika zostało zablokowane.';
  }
  return 'Konto użytkownika zostało tymczasowo zablokowane.';
});
</script>

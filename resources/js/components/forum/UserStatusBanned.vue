<template>
  <span
    v-if="props.isAuthor"
    class="mx-1 user-status user-status--author text-nowrap"
    title="Osoba, która stworzyła wątek na forum.">
    OP
  </span>
  <span
    v-if="props.user.is_blocked"
    class="mx-1 user-status user-status--banned text-nowrap"
    :title="blockTitle">
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
  isAuthor: boolean;
}

const blockTitle = computed(() => {
  if (props.user.is_blocked_perm) {
    return 'Konto użytkownika zostało zablokowane.';
  }
  return 'Konto użytkownika zostało tymczasowo zablokowane.';
});
</script>

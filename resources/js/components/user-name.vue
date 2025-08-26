<template>
  <component
    :is="tagName"
    :href="`/Profile/${user.id}`"
    :data-user-id="user.id"
    class="username"
    :class="user.is_deleted ? 'user-deleted' : 'neon-color-link'"
    :title="user.is_deleted ? 'Użytkownik usunął swoje konto.' : ''"
    v-text="user.name"/>
</template>

<script lang="ts">
import mixins from './mixins/user.js';

export default {
  name: 'user-name',
  mixins: [mixins],
  props: {
    user: {type: Object, required: true},
  },
  computed: {
    tagName(): string {
      return this.user.is_deleted ? 'span' : 'a';
    },
  },
};
</script>

<template>
  <component
    :is="tagName"
    :href="`/Profile/${user.id}`"
    :data-user-id="user.id"
    class="username neon-color-link"
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

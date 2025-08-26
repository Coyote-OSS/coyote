<template>
  <component
    :is="tagName"
    :href="`/Profile/${user.id}`"
    :data-user-id="user.id"
    class="username"
    :class="owner ? 'neon-username-author': 'neon-color-link'"
    :style="{
      textDecoration: this.user.is_deleted ? 'line-through' : '',
      marginRight: this.user.is_deleted ? '3px': '0', 
    }"
    v-text="user.name"/>
</template>

<script lang="ts">
import mixins from './mixins/user.js';

export default {
  name: 'user-name',
  mixins: [mixins],
  props: {
    user: {type: Object, required: true},
    owner: {type: Boolean, default: false},
  },
  computed: {
    tagName(): string {
      return this.user.is_deleted ? 'span' : 'a';
    },
  },
};
</script>

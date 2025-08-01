<template>
  <vue-post
    :tree-topic-post-first="treeTopicPostFirst"
    @reply="reply"
    :post="post"
    v-if="!is_incognito(post.user)"
    :tree-item="treeItem"
  />
</template>

<script lang="ts">
import {mapGetters} from "vuex";
import {Post} from "../../types/models";
import VuePost from '../forum/post.vue';

export default {
  components: {VuePost},
  emits: ['reply'],
  props: {
    post: {type: Object, required: true},
    treeItem: {type: Object, required: false},
    treeTopicPostFirst: {type: Boolean, required: false},
  },
  methods: {
    reply(post: Post, scrollIntoForm: boolean): void {
      this.$emit('reply', post, scrollIntoForm);
    },
  },
  computed: {
    ...mapGetters('topics', ['is_incognito']),
  },
};
</script>

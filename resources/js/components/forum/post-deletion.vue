<template>
  <span>
    <vue-icon name="postDeleted"/>
    Post użytkownika
    <vue-username v-if="post.user" :user="post.user"/>
    <span v-else v-text="post.user_name"/>,
    napisany
    <vue-timeago :datetime="post.created_at"/>,
    <template v-if="deletedByAuthor">
       został usunięty przez autora
      <vue-timeago :datetime="post.deleted_at"/>
    </template>
    <template v-else>
       został usunięty
      <vue-timeago :datetime="post.deleted_at"/>
      <template v-if="post.deleter_name">
        {{ ' przez ' }}
        <strong>{{ post.deleter_name }}</strong>
      </template>
    </template>
    {{ '. ' }}
    <template v-if="post.delete_reason">
      Powód: {{ post.delete_reason }}.
    </template>
  </span>
</template>

<script lang="ts">
import {VueTimeAgo} from "../../plugins/timeago.js";
import VueIcon from "../icon";
import VueUserName from "../user-name.vue";

export default {
  name: 'post-deletion',
  components: {
    'vue-icon': VueIcon,
    'vue-timeago': VueTimeAgo,
    'vue-username': VueUserName,
  },
  props: {
    post: {type: Object, required: true},
  },
  computed: {
    deletedByAuthor(): boolean {
      return !!this.post.deleter_id && this.post.deleter_id === this.post.user_id;
    },
  },
};
</script>

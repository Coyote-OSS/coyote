<template>
  <div :id="anchor" :class="{'highlight-flash': highlight, 'not-read': comment.is_read === false}" class="post-comment">
    <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"/>
    <div :class="{'comment-delete-border':authorBlocked, 'comment-delete-border--expanded':authorBlocked && blockedExpanded}">
      <div class="comment-delete p-2 cursor-pointer" v-if="authorBlocked" @click="blockedToggle">
        <vue-icon name="postCommentAuthorBlocked"/>
        Treść komentarza została ukryta, ponieważ autorem jest zablokowany przez Ciebie użytkownik.
      </div>
      <div v-if="!authorBlocked || blockedExpanded">
        <vue-comment-form
          v-if="comment.is_editing && !authorBlocked"
          :comment="comment"
          @save="$store.commit('posts/editEnd', comment)"
          @cancel="$store.commit('posts/editEnd', comment)"
          ref="comment-form"/>
        <div class="d-flex" v-else>
          <div>
            <div class="neon-avatar-border">
              <vue-avatar v-bind="comment.user" :is-online="comment.user.is_online" class="i-35"/>
            </div>
          </div>
          <div class="ms-2">
            <div>
              <vue-username :user="comment.user"/>
              <vue-user-status-banned class="ms-1 me-1" :user="comment.user" :is-author="comment.user.id === topic.owner_id"/>
              {{' '}}
              <a :href="comment.url">
                <vue-timeago :datetime="comment.created_at" class="text-muted small"/>
              </a>
              <template v-if="!authorBlocked">
                <span v-if="comment.editable" @click="edit" title="Edytuj ten komentarz" class="btn-comment cursor-pointer">
                  <vue-icon name="postCommentEdit"/>
                </span>
                <span v-if="comment.editable" @click="deleteComment" title="Usuń ten komentarz" class="btn-comment cursor-pointer">
                  <vue-icon name="postCommentDelete"/>
                </span>
                <span v-if="comment.editable" @click="migrate" title="Zamień w post" class="btn-comment cursor-pointer">
                  <vue-icon name="postCommentConvertToPost"/>
                </span>
                <span :data-metadata="comment.metadata" :data-url="comment.url" title="Zgłoś ten komentarz"
                      class="btn-comment cursor-pointer">
                  <vue-icon name="postCommentReport"/>
                </span>
              </template>
            </div>
            <span v-html="comment.html" class="comment-text neon-contains-a-color-link"/>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {mapGetters} from "vuex";
import {confirmModal} from "../../plugins/modals";
import {VueTimeAgo} from "../../plugins/timeago.js";
import store from "../../store/index";
import {nextTick} from "../../vue";
import VueAvatar from "../avatar.vue";
import VueFlag from "../flags/flag.vue";
import VueIcon from "../icon";
import {default as mixins} from '../mixins/user.js';
import VueUserName from '../user-name.vue';
import VueCommentForm from "./comment-form.vue";
import UserStatusBanned from "./UserStatusBanned.vue";

export default {
  name: 'comment',
  mixins: [mixins],
  components: {
    VueIcon,
    'vue-username': VueUserName,
    'vue-avatar': VueAvatar,
    'vue-comment-form': VueCommentForm,
    'vue-flag': VueFlag,
    'vue-timeago': VueTimeAgo,
    'vue-user-status-banned': UserStatusBanned,
  },
  props: {
    comment: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      isEditing: false,
      blockedExpanded: false,
    };
  },
  computed: {
    ...mapGetters('topics', ['topic']),
    anchor() {
      return `comment-${this.comment.id}`;
    },
    highlight() {
      return '#' + this.anchor === window.location.hash;
    },
    flags() {
      return [
        ...store.getters['flags/filter'](this.comment.id, 'Coyote\\Comment'),
        ...store.getters['flags/filter'](this.comment.id, 'Coyote\\Post\\Comment'),
      ];
    },
    authorBlocked(): boolean {
      return store.getters['user/isBlocked'](this.comment.user.id);
    },
  },
  methods: {
    blockedToggle() {
      this.$data.blockedExpanded = !this.$data.blockedExpanded;
    },
    edit() {
      store.commit('posts/editStart', this.comment);
      nextTick(() => this.$refs['comment-form'].focus());
    },
    deleteComment() {
      confirmModal({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć komentarz?',
        okLabel: 'Tak, usuń',
      })
        .then(() => store.dispatch('posts/deleteComment', this.comment));
    },
    migrate() {
      confirmModal({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Zamienić na post?',
        okLabel: 'Tak, zamień',
      })
        .then(() => {
          store
            .dispatch('posts/migrateComment', this.comment)
            .then(response => window.location.href = response.data.url);
        });
    },
  },
};
</script>

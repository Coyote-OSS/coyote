<template>
  <div :id="anchor" :class="{'highlight-flash': highlight, 'not-read': comment.is_read === false, 'border border-danger': comment.deleted_at}" class="media">
    <div class="me-2">
      <a v-profile2="comment.user">
        <div class="neon-avatar-border">
          <vue-avatar v-bind="comment.user" :is-online="comment.user.is_online" class="i-35"/>
        </div>
      </a>
    </div>

    <div class="media-body d-flex">
      <vue-comment-form
        v-if="comment.is_editing"
        :microblog="comment"
        ref="form"
        class="w-100 me-1"
        editing
        @cancel="edit(comment)"
        @save="edit(comment)"/>
      <div v-if="!comment.is_editing" class="w-100">
        <vue-username :user="comment.user"/>
        <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"/>
        <div class="comment-text neon-contains-a-color-link" v-html="comment.html"/>
        <ul class="d-none d-sm-block list-inline list-inline-bullet-sm microblog-comment-list small m-0">
          <li class="list-inline-item">
            <a :href="comment.url" class="text-muted cursor-pointer">
              <vue-timeago :datetime="comment.created_at"/>
            </a>
          </li>
          <li class="list-inline-item">
            <span
              @click="checkAuth(vote, comment)"
              @mouseenter.once="loadVoters(comment)"
              :aria-label="commentVoters"
              :class="{'vote-active': comment.is_voted}"
              class="text-muted cursor-pointer"
              data-balloon-pos="up"
              data-balloon-break>
              {{ commentLabel }}
            </span>
          </li>
          <li class="list-inline-item">
            <span @click="checkAuth(reply)" class="text-muted cursor-pointer">
              Odpowiedz
            </span>
          </li>
          <li v-if="isAuthorized" class="list-inline-item">
            <span class="text-muted cursor-pointer" @click="flagComment">
              Zgłoś
            </span>
          </li>
        </ul>

        <ul class="d-sm-none list-inline text-muted small m-0">
          <li class="list-inline-item">
            <a :href="comment.url" class="text-muted">
              <vue-timeago :datetime="comment.created_at"/>
            </a>
          </li>
          <li class="list-inline-item">
            <a href="#" class="text-muted" data-bs-toggle="dropdown">
              <vue-icon name="microblogCommentMenuEditRemove"/>
            </a>
            <div class="dropdown-menu">
              <a @click="checkAuth(reply)" href="javascript:" class="dropdown-item text-muted">
                Odpowiedz
              </a>
              <a @click="checkAuth(vote, comment)"
                 :title="commentVoters"
                 :class="{'text-primary': comment.is_voted, 'text-muted': !comment.is_voted}"
                 href="javascript:"
                 class="dropdown-item">
                {{ commentLabel }}
              </a>
            </div>
          </li>
        </ul>
      </div>

      <div v-if="isAuthorized" class="dropdown">
        <button class="btn btn-xs border-0 small text-muted" type="button" data-bs-toggle="dropdown" aria-label="Dropdown">
          <vue-icon name="microblogCommentMenuAnswerFlag"/>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
          <template v-if="comment.permissions.update">
            <template v-if="!comment.deleted_at">
              <a @click="edit(comment)" class="dropdown-item btn-sm-edit" href="javascript:">
                <vue-icon name="microblogCommentEdit"/>
                Edytuj
              </a>
              <a @click="deleteItem" class="dropdown-item btn-sm-remove" href="javascript:">
                <vue-icon name="microblogCommentDelete"/>
                Usuń
              </a>
            </template>
            <a v-else @click="restoreItem" class="dropdown-item" href="javascript:">
              <vue-icon name="microblogCommentRestore"/>
              Przywróć
            </a>
            <div v-if="comment.user.id !== user.id" class="dropdown-divider"/>
          </template>
          <a v-if="comment.user.id !== user.id" @click="block(comment.user)" href="javascript:" class="dropdown-item">
            <vue-icon name="microblogCommentBlockAuthor"/>
            Zablokuj użytkownika
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {mapActions, mapGetters, mapState} from "vuex";

import declination from '../../libs/declination';
import {openFlagModal} from "../../plugins/flags";
import {VueTimeAgo} from '../../plugins/timeago.js';
import store from "../../store/index";
import VueAvatar from '../avatar.vue';
import VueFlag from '../flags/flag.vue';
import VueIcon from "../icon";
import {MicroblogMixin} from "../mixins/microblog";
import mixins from '../mixins/user.js';
import VueUserName from '../user-name.vue';
import VueCommentForm from "./comment-form.vue";

export default {
  name: 'comment',
  mixins: [mixins, MicroblogMixin],
  store,
  components: {
    VueIcon,
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-comment-form': VueCommentForm,
    'vue-flag': VueFlag,
    'vue-timeago': VueTimeAgo,
  },
  props: {
    comment: {
      type: Object,
      required: true,
    },
  },
  methods: {
    ...mapActions('microblogs', ['vote', 'loadVoters']),
    flagComment(): void {
      openFlagModal(this.$props.comment.url, this.$props.comment.metadata);
    },
    reply() {
      this.$emit('reply', this.comment.user);
    },
    deleteItem() {
      this.delete('microblogs/deleteComment', this.comment);
    },
    restoreItem() {
      store.dispatch('microblogs/restoreComment', this.comment);
    },
  },
  computed: {
    ...mapState('user', ['user']),
    ...mapGetters('user', ['isAuthorized']),
    anchor() {
      return `comment-${this.comment.id}`;
    },
    highlight() {
      return '#' + this.anchor === window.location.hash;
    },
    commentLabel() {
      return this.comment.votes + ' ' + declination(this.comment.votes, ['głos', 'głosy', 'głosów']);
    },
    commentVoters() {
      return this.splice(this.comment.voters);
    },
    flags() {
      return store.getters['flags/filter'](this.comment.id, 'Coyote\\Microblog');
    },
  },
};
</script>

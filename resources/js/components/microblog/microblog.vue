<template>
  <!-- we use below ID in mounted() method -->
  <div :id="`entry-${microblog.id}`" class="microblog neon-tile mb-3 p-3" style="border-radius:8px;">
    <div>
      <div v-if="microblog.deleted_at" class="alert alert-danger">
        Ten wpis został usunięty. Możesz go przywrócić jeżeli chcesz.
      </div>
      <div class="d-flex">
        <div class="d-none d-sm-block me-2">
          <a v-profile2="microblog.user">
            <div class="neon-avatar-border">
              <vue-avatar v-bind="microblog.user" :is-online="microblog.user.is_online" class="i-45"/>
            </div>
          </a>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
          <div class="d-flex flex-nowrap">
            <div class="flex-shrink-0 me-auto">
              <h5 class="media-heading">
                <vue-username :user="microblog.user"/>
              </h5>
              <ul class="list-inline mb-0 list-inline-bullet-sm">
                <li class="list-inline-item text-muted">
                  <a :href="microblog.url" class="small">
                    <vue-timeago :datetime="microblog.created_at"/>
                  </a>
                </li>
                <li class="list-inline-item small text-muted">
                  {{ microblog.views }}
                  {{ declination(microblog.views, ['wyświetlenie', 'wyświetlenia', 'wyświetleń']) }}
                </li>
                <li v-if="microblog.is_sponsored" class="list-inline-item small text-muted">
                  Sponsorowane
                </li>
              </ul>
            </div>
            <div class="microblog-tags">
              <vue-tags :tags="microblog.tags" class="tag-clouds-md"/>
            </div>
            <div v-if="isAuthorized" class="dropdown">
              <button class="btn btn-xs border-0 text-muted" type="button" data-bs-toggle="dropdown" aria-label="Dropdown">
                <vue-icon name="microblogMenuDropdown"/>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <template v-if="microblog.permissions.update">
                  <template v-if="!microblog.deleted_at">
                    <a @click="edit(microblog)" class="dropdown-item" href="javascript:">
                      <vue-icon name="microblogEdit"/>
                      Edytuj
                    </a>
                    <a @click="deleteItem" class="dropdown-item" href="javascript:">
                      <vue-icon name="microblogDelete"/>
                      Usuń
                    </a>
                  </template>
                  <a v-else @click="restoreItem" class="dropdown-item" href="javascript:">
                    <vue-icon name="microblogRestore"/>
                    Przywróć
                  </a>
                  <a v-if="microblog.permissions.moderate && !microblog.deleted_at"
                     @click="toggleSponsored(microblog)"
                     class="dropdown-item" href="javascript:">
                    <vue-icon name="microblogSponsored"/>
                    Sponsorowany
                  </a>
                  <div v-if="microblog.user.id !== user.id" class="dropdown-divider"/>
                </template>
                <a v-if="microblog.user.id !== user.id" @click="block(microblog.user)" href="javascript:" class="dropdown-item">
                  <vue-icon name="microblogBlockAuthor"/>
                  Zablokuj użytkownika
                </a>
              </div>
            </div>
          </div>

          <div v-show="!microblog.is_editing">
            <vue-see-more>
              <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"/>
              <div ref="microblog-text" v-html="microblog.html" class="microblog-text neon-contains-a-color-link"/>

              <a v-if="opg" :href="opg.metadata.url" :title="opg.metadata.title" class="card microblog-opg" target="_blank">
                <div class="microblog-opg-image" :style="`background-image: url(${opg.url})`"/>
                <div class="card-body">
                  <h5 class="text-truncate mb-0">{{ opg.metadata.title }}</h5>
                  <p class="text-truncate">{{ opg.metadata.description }}</p>
                  <small class="text-muted">{{ opg.metadata.url }}</small>
                </div>
              </a>

              <div v-if="images.length" class="row mb-2">
                <div
                  v-for="(image, imageIndex) in images"
                  :key="imageIndex"
                  class="col-6 col-md-3"
                >
                  <a @click.prevent="index = imageIndex" :href="image.url">
                    <img :alt="`Załącznik ${imageIndex}`" class="img-thumbnail" :src="image.thumbnail">
                  </a>
                </div>
              </div>
            </vue-see-more>
          </div>
          <vue-form
            v-if="microblog.is_editing"
            ref="form"
            :microblog="microblog"
            class="mt-2 mb-2"
            @cancel="edit(microblog)"
            @save="edit(microblog)"/>
          <div class="microblog-actions">
            <span
              @click="checkAuth(vote, microblog)"
              @mouseenter.once="loadVoters(microblog)"
              class="microblog-action"
              :aria-label="voters"
              data-balloon-pos="up"
              data-balloon-break>
              <vue-icon name="microblogVoted" v-if="microblog.is_voted" class="neon-primary-color"/>
              <vue-icon name="microblogVote" v-else/>
              {{ ' ' }}
              {{ microblog.votes }} {{ declination(microblog.votes, ['głos', 'głosy', 'głosów']) }}
            </span>
            <span @click="checkAuth(subscribe, microblog)" class="microblog-action" title="Wł/Wył obserwowanie tego wpisu">
              <vue-icon name="microblogSubscribed" v-if="microblog.is_subscribed" class="neon-subscribe neon-subscribe--active"/>
              <vue-icon name="microblogSubscribe" v-else class="neon-subscribe"/>
              {{ ' ' }}
              <span class="d-none d-sm-inline">Obserwuj</span>
            </span>
            <span @click="checkAuth(reply, microblog.user)" class="microblog-action" title="Odpowiedz na ten wpis">
              <vue-icon name="microblogAddComment"/>
              {{ ' ' }}
              <span class="d-none d-sm-inline">Komentuj</span>
            </span>
            <span @click="copy" class="microblog-action" title="Kopiuj link do schowka">
              <vue-icon name="microblogShare"/>
              {{ ' ' }}
              <span class="d-none d-sm-inline">Udostępnij</span>
            </span>
            <span v-if="isAuthorized" :data-metadata="microblog.metadata" :data-url="microblog.url" class="microblog-action" title="Zgłoś ten wpis">
              <vue-icon name="microblogReport"/>
              {{ ' ' }}
              <span class="d-none d-sm-inline">Zgłoś</span>
            </span>
          </div>
          <div ref="comments" class="microblog-comments">
            <div v-if="microblog.comments_count > Object.keys(microblog.comments).length" class="show-all-comments">
              <span @click="loadComments(microblog)" class="cursor-pointer">
                <vue-icon name="microblogCommentsFoldedUnfold"/>
                Zobacz
                {{ declination(totalComments, ['pozostały', 'pozostałe', 'pozostałe']) }}
                {{ totalComments }}
                {{ declination(totalComments, ['komentarz', 'komentarze', 'komentarzy']) }}
              </span>
            </div>

            <vue-comment v-for="comment in microblog.comments" :key="comment.id" :comment="comment" @reply="reply"/>

            <form v-if="isAuthorized" method="POST">
              <div class="media">
                <a v-profile2="user">
                  <div class="neon-avatar-border i-35">
                    <vue-avatar
                      :photo="user.photo"
                      :name="user.name"
                      :initials="user.initials"
                      class="d-block"/>
                  </div>
                </a>
                <div class="media-body position-relative ms-1">
                  <vue-comment-form :microblog="commentDefault" ref="comment-form"/>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <vue-gallery :images="imageUrls" :index="index" @close="index = null"/>
  </div>
</template>

<script lang="ts">
import {mapActions, mapGetters, mapState} from "vuex";

import IsImage from '../../libs/assets';
import useBrackets from "../../libs/prompt";
import {copyToClipboard} from '../../plugins/clipboard';
import {VueTimeAgo} from '../../plugins/timeago.js';
import store from "../../store";
import {notify} from "../../toast";
import {Asset, User} from '../../types/models';
import VueAvatar from '../avatar.vue';
import VueFlag from '../flags/flag.vue';
import VueIcon from "../icon";
import {MicroblogMixin} from "../mixins/microblog";
import {default as mixins} from '../mixins/user.js';
import VueSeeMore from "../seeMore/seeMore.vue";
import VueTags from '../tags.vue';
import VueUserName from "../user-name.vue";

import VueCommentForm from './comment-form.vue';
import VueComment from "./comment.vue";
import VueForm from './form.vue';
import VueGallery from "./gallery.vue";

export default {
  name: 'microblog',
  mixins: [mixins, MicroblogMixin],
  store,
  components: {
    VueSeeMore,
    VueIcon,
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-comment': VueComment,
    'vue-form': VueForm,
    'vue-comment-form': VueCommentForm,
    'vue-gallery': VueGallery,
    'vue-flag': VueFlag,
    'vue-tags': VueTags,
    'vue-timeago': VueTimeAgo,
  },
  props: {
    wrap: {type: Boolean},
  },
  data() {
    return {
      index: null as number|null,
      commentDefault: {parent_id: this.microblog.id, text: '', assets: []},
    };
  },
  mounted() {
    const pageHitHandler = () => {
      const rect = this.$refs['microblog-text'].getBoundingClientRect();

      if (rect.top >= 0 && rect.top <= window.innerHeight) {
        document.removeEventListener('scroll', pageHitHandler);
        store.dispatch('microblogs/hit', this.microblog);

        return true;
      }

      return false;
    };

    if (!pageHitHandler()) {
      document.addEventListener('scroll', pageHitHandler);
    }
  },
  methods: {
    ...mapActions('microblogs', ['vote', 'subscribe', 'loadVoters', 'loadComments', 'toggleSponsored']),

    reply(user: User) {
      this.$data.commentDefault.text += `@${useBrackets(user.name)}: `;
      this.$refs['comment-form'].focus();
    },

    deleteItem() {
      this.delete('microblogs/delete', this.microblog);
    },

    restoreItem() {
      store.dispatch('microblogs/restore', this.microblog);
    },

    copy() {
      if (copyToClipboard(this.microblog.url)) {
        notify({type: 'success', text: 'Link skopiowany do schowka.'});
      } else {
        notify({type: 'error', text: 'Nie można skopiować linku do schowka.'});
      }
    },
    focus() {
      this.$refs['comment-form'].focus();
    },
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),
    ...mapState('user', ['user']),

    voters() {
      return this.splice(this.microblog.voters);
    },

    totalComments() {
      return this.microblog.comments_count! - Object.keys(this.microblog.comments).length;
    },

    images(): Asset[] {
      return this
        .microblog
        .assets
        .filter(asset => IsImage(asset.name!) && !asset.metadata);
    },
    imageUrls(): string[] {
      return this.images.map(asset => asset.url);
    },

    opg() {
      return this
        .microblog
        .assets
        .find(asset => asset.metadata);
    },

    flags() {
      return store.getters['flags/filter'](this.microblog.id, 'Coyote\\Microblog');
    },
  },
};
</script>

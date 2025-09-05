<template>
  <div class="card-body topic-list-item neon-zebra__list-item"
       :class="{'not-read': !topic.is_read, 'flagged': flag != null, 'tagged': highlight}">
    <div class="row">
      <div
        :class="showCategoryName ? 'col-xl-9 col-lg-10' : 'col-xl-10 col-lg-10'"
        class="col-md-12 d-flex align-items-center"
      >
        <a
          @click.left="mark"
          :href="getUrl()"
          :class="{'not-read': !topic.is_read}"
          class="topic-icon me-2 d-none d-md-flex neon-color-link"
        >
          <vue-icon name="topicStateSticky" v-if="topic.is_sticky"/>
          <vue-icon name="topicStateLocked" v-else-if="topic.is_locked"/>
          <vue-icon name="topicStateStandard" v-else/>
        </a>

        <div class="topic-container">
          <div class="topic-row">
            <h5 class="topic-subject text-truncate m-0">
              <span v-if="isAuthorized" @click="subscribe(topic)" title="Kliknij aby wł/wył obserwowanie wątku" class="cursor-pointer">
                <vue-icon name="topicSubscribed" v-if="topic.is_subscribed" class="neon-subscribe neon-subscribe--active"/>
                <vue-icon name="topicSubscribe" v-else class="neon-subscribe"/>
                {{' '}}
              </span>
              <a v-if="topic.accepted_id" :href="topic.url + `?p=${topic.accepted_id}#id${topic.accepted_id}`">
                <span class="topic-answer" :class="topic.customAcceptBadge ? 'topic-answer--fixed' : 'topic-answer--answered'">
                  <vue-icon name="topicAccepted"/>
                  {{topic.customAcceptBadge ? 'Gotowe' : 'Zaakceptowano'}}
                </span>
                {{' '}}
              </a>
              <a :href="getUrl()" :class="{'topic-unread': !topic.is_read, 'neon-color-link': !topic.is_read}">
                {{topic.title}}
              </a>
              <small v-if="showCategoryName" class="d-inline d-xl-none">
                w
                <a :href="topic.forum.url">
                  {{topic.forum.name}}
                </a>
              </small>
              <a v-if="flag != null" :href="flag" title="Przejdź do raportowanego posta">
                {{' '}}
                <vue-icon name="topicReported"/>
              </a>
            </h5>

            <div v-if="totalPages > 1 && !isTree" class="d-none d-sm-inline ms-2 topic-pagination">
              <vue-icon name="topicPages" class="neon-color-link"/>
              {{' '}}
              <a :href="topic.url + '?page=1'" class="neon-color-link">1</a>
              {{' '}}
              <template v-if="totalPages > 4">
                ...
                {{' '}}
              </template>
              <a v-if="totalPages === 4" :href="topic.url + '?page=2'" class="neon-color-link">
                2
                {{' '}}
              </a>
              <template v-for="i in paginatorPages">
                <a :href="topic.url + '?page=' + i" class="neon-color-link">
                  {{i}}
                </a>
                {{' '}}
              </template>
            </div>

            <ul class="list-inline small mt-1 mt-sm-0 mb-0 d-block d-sm-inline ms-sm-auto flex-sm-shrink-0">
              <li class="list-inline-item small" title="Liczba odpowiedzi">
                <vue-icon name="topicRepliesReplyPresent" v-if="topic.is_replied" class="topic-has-reply neon-topic-replies-icon"/>
                <vue-icon name="topicRepliesReplyMissing" v-else/>
                {{number(topic.replies)}}
              </li>
              <li class="list-inline-item small" title="Liczba wyświetleń">
                <vue-icon name="topicViews"/>
                {{number(topic.views)}}
              </li>
              <li v-if="topic.score > 0" class="list-inline-item small" title="Liczba głosów oddanych na ten wątek">
                <vue-icon name="topicVotesVotePresent" v-if="topic.is_voted" class="neon-primary-color"/>
                <vue-icon name="topicVotesVoteMissing" v-else/>
                {{number(topic.score)}}
              </li>
            </ul>
          </div>

          <div class="d-flex mt-1">
            <div class="d-none d-lg-inline small text-truncate">
              <a :href="topic.url + `?p=${topic.first_post_id}#id${topic.first_post_id}`">
                <vue-timeago :datetime="topic.created_at"/>
              </a>,
              <vue-username v-if="topic.user" :user="topic.user" class="mt-1"/>
              <span v-else>{{topic.user_name}}</span>
            </div>
            <ul v-if="topic.tags.length" class="tag-clouds tag-clouds-xs">
              <li v-for="tag in topic.tags">
                <a :href="tag.url" class="neon-tag">
                  {{tag.name}}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="showCategoryName" class="col-xl-1 d-none d-xl-block text-center text-truncate">
        <a :href="topic.forum.url" class="small" :title="topic.forum.name">
          {{topic.forum.name}}
        </a>
      </div>

      <div class="col-xl-2 col-lg-2 col-md-12">
        <div class="media m-md-0">
          <a v-profile2="this.topic.last_post.user">
            <div class="neon-avatar-border me-2 d-none d-md-inline-block">
              <vue-avatar v-bind="topic.last_post.user" class="i-35"/>
            </div>
          </a>
          <div class="media-body small text-truncate">
            <p class="mb-0 d-inline d-md-block">
              <vue-username v-if="topic.last_post.user" :user="topic.last_post.user"/>
              <span class="topic-username" v-else v-text="topic.last_post.user_name"/>
            </p>
            {{' '}}
            <a :href="topic.url + `?p=${topic.last_post.id}#id${topic.last_post.id}`" title="Zobacz ostatni post">
              <vue-timeago :datetime="topic.last_post.created_at"/>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import {VueTimeAgo} from '../../plugins/timeago.js';
import store from '../../store/index';
import VueAvatar from '../avatar.vue';
import VueIcon from '../icon';
import {default as mixins} from '../mixins/user';
import VueUserName from '../user-name.vue';

export default {
  mixins: [mixins],
  components: {
    VueIcon,
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-timeago': VueTimeAgo,
  },
  props: {
    topic: {
      type: Object,
      require: true,
    },
    postsPerPage: {
      type: Number,
      default: 10,
    },
    highlight: {
      type: Boolean,
    },
    showCategoryName: {
      type: Boolean,
    },
  },
  methods: {
    getUrl() {
      const postUrl = id => `${this.topic.url}?p=${id}#id${id}`;

      // redirect straight to specific post if this field is present
      if (this.topic.user_post_id) {
        return postUrl(this.topic.user_post_id);
      }

      // redirect to last post if topic has been read by registered user.
      if (this.topic.is_read && (this.isAuthorized && this.topic.last_post_created_at > store.state.user.user.created_at)) {
        if (this.$props.topic.discuss_mode === 'tree') {
          return this.$props.topic.url;
        }
        return postUrl(this.topic.last_post.id);
      }
      return this.topic.url;
    },

    mark(event) {
      if (this.topic.is_read) {
        return;
      }
      store.dispatch('topics/mark', this.topic);
      event.preventDefault();
    },

    ...mapActions('topics', ['subscribe']),
  },
  computed: {
    isTree() {
      return this.$props.topic.discuss_mode === 'tree';
    },
    totalPages() {
      return Math.ceil((this.topic.replies + 1) / this.postsPerPage);
    },

    paginatorPages() {
      let pages = [];

      for (let i = Math.max(2, this.totalPages - 1); i <= this.totalPages; i++) {
        pages.push(i);
      }

      return pages;
    },

    flag() {
      const flags = store.getters['flags/filter'](this.topic.id, 'Coyote\\Topic');
      return flags.length ? flags[0].url : null;
    },

    ...mapGetters('user', ['isAuthorized']),
  },
};
</script>

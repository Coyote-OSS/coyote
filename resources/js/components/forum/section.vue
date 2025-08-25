<template>
  <div class="neon-tile neon-rounded mb-3">
    <div class="pb-2 pt-1 ps-lg-3 pt-lg-2 pe-lg-2 neon-zebra__header neon-rounded-top">
      <div class="d-flex justify-content-between">
        <div class="cursor-pointer">
          <span v-if="collapsable" @click="collapse">
            <vue-icon name="categorySectionFolded" v-if="isCollapse"/>
            <vue-icon name="categorySectionFold" v-else/>
            {{ name }}
          </span>
          <template v-else v-text="name"/>
        </div>
        <div
          v-if="isAuthorized && !categories[0].parent_id"
          class="dropdown dropleft"
          :class="{'open': isDropdown}"
          v-click-away="hideDropdown"
        >
          <span @click="isDropdown = !isDropdown" class="card-cog mt-2 me-2">
            <vue-icon name="categorySectionMenu"/>
          </span>
          <div :class="{'d-block': isDropdown}" class="dropdown-menu">
            <span v-for="category in categories" class="dropdown-item" @click="toggle(category)">
              <vue-icon name="categorySectionMenuItemEnabled" v-if="!category.is_hidden"/>
              <vue-icon empty v-else/>
              {{ category.name }}
            </span>
          </div>
        </div>
      </div>
    </div>
    <section v-if="!isCollapse" class="card-categories mb-0">
      <template v-for="(category, index) in categories">
        <div v-if="!category.is_hidden" :class="{'not-read': !category.is_read}" class="px-3 py-2 toolbox-container neon-zebra__list-item">
          <div class="row">
            <div class="col-6 col-md-12 col-lg-5 d-flex align-items-center">
              <a @click="mark(category)" :class="{'not-read': !category.is_read}" class="d-none d-lg-block position-relative me-2 neon-color-link">
                <span v-if="category.is_locked" class="logo">
                  <vue-icon name="forumCategoryLocked"/>
                </span>
                <span v-else :class="['logo', className(category.name)]">
                  <vue-icon name="forumCategory"/>
                </span>
              </a>
              <div class="overflow-hidden">
                <h3>
                  <a :href="category.url">
                    {{ category.name }}
                  </a>
                </h3>
                <vue-tags v-if="category.enable_tags && !category.children" :tags="category.tags" class="tag-clouds-sm"/>
                <ul v-if="category.children" class="list-inline list-sub d-md-block d-lg-block">
                  <li v-for="child in category.children" class="list-inline-item">
                    <vue-icon name="categorySectionChildWasRead" v-if="child.is_read"/>
                    <i v-else class="not-read" title="Nowe posty w tej kategorii"/>
                    {{ ' ' }}
                    <a :href="child.url">
                      {{ child.name }}
                    </a>
                    {{ ' ' }}
                  </li>
                </ul>
              </div>
            </div>
            <div v-if="!category.is_redirected" class="col-6 col-md-12 col-lg-2 d-flex align-items-center">
              <ul class="list-inline mb-0 mt-1">
                <li class="list-inline-item">
                  <strong>{{ number(category.topics) }}</strong>
                  {{ ' ' }}
                  <small class="text-muted">
                    {{ declination(category.topics, ['wątek', 'wątków', 'wątków']) }}
                  </small>
                </li>
                <li class="list-inline-item">
                  <strong>{{ number(category.posts) }}</strong>
                  {{ ' ' }}
                  <small class="text-muted">
                    {{ declination(category.posts, ['post', 'postów', 'postów']) }}
                  </small>
                </li>
              </ul>
            </div>

            <div v-else class="col-12 col-lg-7 text-center">
              Liczba przekierowań:
              {{ ' ' }}
              {{ category.redirects }}
            </div>

            <div v-if="!category.is_redirected" class="col-12 col-lg-5 position-relative">
              <div v-if="category.post" class="media">
                <a v-profile2="category.post.user">
                  <div class="neon-avatar-border d-none d-sm-block me-2">
                    <vue-avatar v-bind="category.post.user" class="i-38"/>
                  </div>
                </a>
                <div class="media-body overflow-hidden">
                  <p class="text-truncate mb-1">
                    <a :href="getUrl(category)">
                      {{ category.topic.title }}
                    </a>
                  </p>
                  <span class="text-muted">
                    <vue-timeago :datetime="category.post.created_at"/>
                  </span>,
                  <vue-username v-if="category.post.user" :user="category.post.user"/>
                  <span v-else>{{ category.post.user_name }}</span>
                  <div class="toolbox">
                    <a href="javascript:" title="Oznacz jako przeczytane" @click="mark(category)">
                      <vue-icon name="categorySectionMarkAsRead"/>
                    </a>
                    <a v-if="isAuthorized" :class="{'disabled': isBeginning(index)}" title="Przesuń w górę" @click="up(category)">
                      <vue-icon name="categorySectionMoveUp"/>
                    </a>
                    <a v-if="isAuthorized" :class="{'disabled': isEnding(index)}" title="Przesuń w dół" @click="down(category)">
                      <vue-icon name="categorySectionMoveDown"/>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </section>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";

import clickAway from '../../clickAway.js';
import {VueTimeAgo} from '../../plugins/timeago.js';
import store from '../../store/index';
import VueAvatar from '../avatar.vue';
import VueIcon from '../icon';
import {default as mixins} from '../mixins/user.js';
import VueTags from '../tags.vue';
import VueUsername from '../user-name.vue';

export default {
  mixins: [mixins],
  directives: {clickAway},
  components: {
    VueIcon,
    'vue-avatar': VueAvatar,
    'vue-username': VueUsername,
    'vue-tags': VueTags,
    'vue-timeago': VueTimeAgo,
  },
  store,
  props: {
    name: {
      type: String,
      required: false, // <-- subcategories might not have section name
    },
    order: {type: Number, required: true},
    categories: {type: Array},
    collapsable: {type: Boolean, default: false},
    isCollapse: {type: Boolean, default: false},
  },
  data() {
    return {
      isDropdown: false,
    };
  },
  methods: {
    collapse() {
      store.dispatch('forums/collapse', this.categories[0]);
      this.$emit('collapse', this.categories[0].id);
    },
    hideDropdown() {
      this.isDropdown = false;
    },
    isBeginning(index) {
      let result = true;
      while (index > 0) {
        if (!this.categories[--index].is_hidden) {
          result = false;
        }
      }
      return result;
    },
    isEnding(index) {
      let result = true;
      const length = this.categories.length - 1;
      while (index < length) {
        if (!this.categories[++index].is_hidden) {
          result = false;
        }
      }
      return result;
    },
    className(name) {
      return 'logo-' + name.toLowerCase().replace(/[^a-z]/g, "");
    },
    getUrl(category) {
      return category.topic.is_read ? `${category.topic.url}?p=${category.post.id}#id${category.post.id}` : category.topic.url;
    },
    ...mapActions('forums', ['mark', 'toggle', 'up', 'down']),
  },
  computed: mapGetters('user', ['isAuthorized']),
};
</script>

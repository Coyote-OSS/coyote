import {mapGetters} from 'vuex';

import VueAvatar from '../components/avatar.vue';
import VueFollowButton from "../components/forms/follow-button.vue";
import VueForm from '../components/microblog/form.vue';
import VueMicroblog from "../components/microblog/microblog.vue";
import VuePagination from '../components/pagination.vue';
import VueUserName from "../components/user-name.vue";
import store from '../store';
import {Flag, Microblog, Paginator, User} from "../types/models";
import {createVueAppNotifications, setAxiosErrorVueNotification} from "../vue";
import {default as LiveMixin} from './microblog/live';

setAxiosErrorVueNotification();

declare global {
  interface Window {
    pagination: Paginator;
    microblog: Microblog;
    flags: Flag[] | undefined;
    popularTags: string[];
    recommendedUsers: User[];
  }
}

createVueAppNotifications('Microblog', '#js-microblog', {
  delimiters: ['${', '}'],
  mixins: [LiveMixin],
  components: {
    'vue-microblog': VueMicroblog,
    'vue-pagination': VuePagination,
    'vue-form': VueForm,
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-follow-button': VueFollowButton,
  },
  store,
  data() {
    return {
      popularTags: window.popularTags,
      recommendedUsers: window.recommendedUsers,
      microblogDefault: {assets: [], tags: [], text: ''},
    };
  },
  created() {
    if ('pagination' in window) {
      store.commit('microblogs/INIT', window.pagination);
    }

    if ('microblog' in window) {
      store.commit('microblogs/ADD', window.microblog!);
    }

    store.commit('flags/init', window.flags);
  },
  mounted() {
    document.getElementById('js-skeleton')?.remove();
    // @ts-ignore
    this.liveNotifications();
  },
  methods: {
    changePage(page: number) {
      window.location.href = `${window.location.href.split('?')[0]}?page=${page}`;
    },

    scrollToMicroblog(microblog: Microblog) {
      window.location.hash = `#entry-${microblog.id}`;
    },
  },
  computed: {
    ...mapGetters('microblogs', ['microblogs', 'currentPage', 'totalPages']),

    microblog(): Microblog {
      return this.microblogs[Object.keys(this.microblogs)[0]];
    },
  },
});

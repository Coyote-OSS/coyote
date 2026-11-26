import axios from "axios";
import {mapGetters} from "vuex";

import VueButton from "../../components/forms/button.vue";
import VueSelect from "../../components/forms/select.vue";
import VueIcon from "../../components/icon";
import {default as mixin} from '../../components/mixins/user';
import VueModal from "../../components/modal.vue";
import {copyToClipboard} from "../../plugins/clipboard";
import store from "../../store";
import {notify} from "../../toast";
import {Forum} from '../../types/models';

export default {
  delimiters: ['${', '}'],
  mixins: [mixin],
  components: {
    'vue-modal': VueModal,
    'vue-button': VueButton,
    'vue-select': VueSelect,
    'vue-icon': VueIcon,
  },
  store,
  data() {
    return {
      topic: window.topic,
      forum: window.forum,
      allForums: window.allForums,
      reasons: window.reasons,
      isProcessing: false,
      forumId: null,
      reasonId: null,
    };
  },
  methods: {
    copyTopicLink(): void {
      this.copy(baseUrl(this.$data.topic.url), 'Link do wątku znajduje się w schowku.');
    },
    copy(text: string, successMessage: string): void {
      if (copyToClipboard(text)) {
        notify({type: 'success', text: successMessage});
      } else {
        notify({type: 'error', text: 'Nie można skopiować linku. Sprawdź ustawienia przeglądarki.'});
      }
    },
    markForums() {
      store.dispatch('forums/markAll');
      store.commit('topics/markAll');
    },

    markTopics() {
      store.dispatch('topics/markAll');
    },

    lock() {
      store.dispatch('topics/lock', this.topic);
    },

    subscribe() {
      store.dispatch('topics/subscribe', this.topic);
    },

    subscribeForum(): void {
      this.forum.is_subscribed = !this.forum.is_subscribed;
      axios.post(`/Forum/${this.forum.slug}/Subscribe`);
    },

    move() {
      this.isProcessing = true;

      store.dispatch('topics/move', {topic: this.topic, forumId: this.forumId, reasonId: this.reasonId})
        .then(result => window.location.href = result.data.url)
        .finally(() => this.isProcessing = false);
    },

    changeTitle() {
      this.isProcessing = true;

      store.dispatch('topics/changeTitle', {topic: this.topic})
        .then(result => window.location.href = result.data.url)
        .finally(() => this.isProcessing = false);
    },
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),
    sortedForums(): Forum[] {
      return this.allForums.map(forum => ({
        ...forum,
        name: '&nbsp;'.repeat(forum.indent * 4) + forum.name,
      }));
    },
  },
};

function baseUrl(postUrl: string): string {
  const url = new URL(postUrl);
  return url.origin + url.pathname;
}

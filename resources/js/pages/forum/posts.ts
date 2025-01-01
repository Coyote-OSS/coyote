import {mapGetters, mapState} from "vuex";

import VueForm from "../../components/forum/form.vue";
import VuePoll from "../../components/forum/poll.vue";
import VuePostWrapper from "../../components/forum/post-wrapper.vue";
import VuePagination from "../../components/pagination.vue";
import {PostCommentSaved, PostSaved, PostVoted, Subscriber} from "../../libs/live";
import store from "../../store/index";
import {notify} from "../../toast";
import {PostOrdering} from "../../treeTopic/postOrdering";
import {Post} from "../../types/models";
import {nextTick} from "../../vue";

export default {
  name: 'Posts',
  delimiters: ['${', '}'],
  components: {
    'vue-post-wrapper': VuePostWrapper,
    'vue-form': VueForm,
    'vue-poll': VuePoll,
    'vue-pagination': VuePagination,
  },
  store,
  data() {
    return {
      showStickyCheckbox: window.showStickyCheckbox,
      showDiscussModeSelect: window.showDiscussModeSelect,
      undefinedPost: {text: '', html: '', assets: []},
      reasons: window.reasons,
      popularTags: window.popularTags,
      postFormHidden: false,
      treePostOrdering: 'orderByCreationDateOldest',
      editorRevealed: false,
    };
  },
  created() {
    store.commit('posts/init', window.pagination);
    store.commit('topics/init', [window.topic]);
    store.commit('topics/setReasons', this.reasons);
    store.commit('forums/init', [window.forum]);
    store.commit('poll/init', window.poll);
    store.commit('flags/init', window.flags);
    this.resetForm();
  },
  mounted() {
    document.getElementById('js-skeleton')?.remove();

    this.liveUpdate();

    const hints = ['hint-title', 'hint-text', 'hint-tags', 'hint-user_name'];

    [
      document.querySelector('#js-submit-form input[name="title"]'),
      document.querySelector('#js-submit-form textarea[name="text"]'),
      document.querySelector('#js-submit-form input[name="tags"]'),
    ].forEach(el => {
      if (!el) {
        return;
      }

      el.addEventListener('focus', () => {
        const name = el.getAttribute('name');
        const hint = document.getElementById(`hint-${name}`);

        if (!hint) {
          return; // hint tooltips might not be present on the website
        }

        hints.forEach(hint => document.getElementById(hint)!.style.display = 'none');
        hint.style.display = 'block';
      });
    });
  },
  methods: {
    revealEditor(): void {
      this.$data.editorRevealed = true;
      nextTick(() => {
        this.markdownRef.focus();
      });
    },
    liveUpdate() {
      const subscriber = new Subscriber(`topic:${window.topic.id}`);

      subscriber.subscribe('CommentSaved', new PostCommentSaved());
      subscriber.subscribe('PostSaved', new PostSaved());
      subscriber.subscribe('PostVoted', new PostVoted());
    },
    changePage(page: number) {
      window.location.href = `?page=${page}`;
    },
    reply(post: Post, scrollIntoForm = true) {
      const username = post.user ? post.user.name : post.user_name!;
      if (scrollIntoForm) {
        this.markdownRef.appendUserMention(username);
        document.getElementById('js-submit-form')!.scrollIntoView();
      } else {
        this.markdownRef.appendBlockQuote(username, post.id, post.text);
        notify({type: 'success', text: 'Cytat został dodany do formularza.'});
      }
    },
    savedForm(post: Post): void {
      this.undefinedPost = {text: '', html: '', assets: []};
      window.location.hash = `id${post.id}`;
      this.resetForm();
    },
    redirectToTopic(post: Post): void {
      this.undefinedPost = {text: '', html: '', assets: []};
      window.location.href = post.url;
    },
    resetForm(): void {
      this.$data.postFormHidden = store.getters['topics/is_mode_tree'];
    },
    changeTreeTopicPostOrdering(event: Event): void {
      const ordering: PostOrdering = event.target!.value;
      store.commit('topics/postOrdering', ordering);
    },
  },
  computed: {
    markdownRef(): VueMarkdown {
      return this.$refs['js-submit-form'].$refs['markdown']!;
    },
    ...mapGetters('posts', ['posts', 'linearTopicPosts', 'treeTopicPostsFirst', 'treeTopicPostsRemaining', 'totalPages', 'currentPage']),
    ...mapGetters('topics', ['topic', 'is_mode_tree', 'is_mode_linear', 'treeTopicPostOrdering']),
    ...mapGetters('user', ['isAuthorized']),
    ...mapState('poll', ['poll']),
  },
};

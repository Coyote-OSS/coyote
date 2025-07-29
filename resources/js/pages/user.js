import axios from 'axios';
import {mapActions, mapGetters, mapState} from 'vuex';

import VueAvatar from '../components/avatar.vue';
import VueFollowButton from '../components/forms/follow-button.vue';
import VueTagsInline from '../components/forms/tags-inline.vue';
import {default as SkillsMixin} from '../components/mixins/skills.js';
import VueModal from '../components/modal.vue';
import VueTags from '../components/tags.vue';
import VueThumbnail from '../components/thumbnail.vue';
import VueUserName from '../components/user-name.vue';
import {VueTimeAgo} from '../plugins/timeago.js';
import store from '../store/index';
import {createVueApp, setAxiosErrorVueNotification} from '../vue';

setAxiosErrorVueNotification();

createVueApp('User', '#js-user', {
  delimiters: ['${', '}'],
  components: {'vue-thumbnail': VueThumbnail},
  methods: {
    setPhoto(data) {
      store.commit('user/update', {photo: data.url});
    },
    deletePhoto() {
      axios.delete('/User/Photo/Delete');
      store.commit('user/update', {photo: null});
    },
  },
  computed: {
    url() {
      return store.state.user.user.photo;
    },
  },
});

createVueApp('Skills', '#js-skills', {
  delimiters: ['${', '}'],
  mixins: [SkillsMixin],
  components: {
    'vue-tags': VueTags,
    'vue-tags-inline': VueTagsInline,
  },
  data() {
    return {
      skills: window.skills,
      rateLabels: window.rateLabels,
    };
  },
  methods: {
    addSkill(tag) {
      const defaultRate = 2;
      axios.post('/User/Skills', {name: tag.name, priority: defaultRate}).then(response => {
        this.skills.push(Object.assign(response.data, {priority: defaultRate}));
      });
    },

    updateSkill(tag) {
      axios.post(`/User/Skills/${tag.id}`, tag);
    },

    deleteSkill(tag) {
      this.skills.splice(this.skills.findIndex(skill => skill.id === tag.id), 1);
      axios.delete(`/User/Skills/${tag.id}`);
    },
  },
});

createVueApp('Followers', '#js-followers', {
  delimiters: ['${', '}'],
  store,
  components: {'vue-avatar': VueAvatar, 'vue-username': VueUserName, 'vue-follow-button': VueFollowButton},
  data() {
    return {
      users: window.users,
    };
  },
  methods: {
    user(userId) {
      return this.users.find(user => user.id === userId);
    },
    ...mapActions('user', ['unfollow']),
  },
  computed: {
    ...mapState('user', ['followers']),
    ...mapGetters('user', ['isBlocked']),
  },
});

createVueApp('Tokens', '#js-tokens', {
  delimiters: ['${', '}'],
  components: {
    'vue-modal': VueModal,
    'vue-timeago': VueTimeAgo,
  },
  data() {
    return {
      tokens: [],
      tokenName: null,
      tokenId: null,
    };
  },
  mounted() {
    this.loadTokens();
  },
  methods: {
    loadTokens() {
      axios.get('/oauth/personal-access-tokens').then(response => this.tokens = response.data);
    },
    addToken() {
      axios.post('/oauth/personal-access-tokens', {name: this.tokenName})
        .then(response => {
          this.tokenId = response.data.accessToken;
          this.loadTokens();
          this.$refs.modal.open();
          this.tokenName = null;
        });
    },
    deleteToken(tokenId) {
      axios.delete(`/oauth/personal-access-tokens/${tokenId}`);
      this.loadTokens();
    },
  },
});

createVueApp('Verification', '#js-verification', {
  delimiters: ['${', '}'],
  data() {
    return {
      userVerified: window.userVerified,
      phoneNumber: '',
      verificationCode: '',
      initiateError: null,
      initiateSuccess: null,
      verificationError: null,
    };
  },
  methods: {
    initiateVerification() {
      this.$data.initiateSuccess = null;
      this.$data.initiateError = null;
      axios.post('/User/Verification/Initiate', {phoneNumber: this.$data.phoneNumber})
        .then(response => {
          if (response.data.initiateResult === 'initiated') {
            this.$data.initiateSuccess = 'Wysłaliśmy wiadomość SMS z kodem weryfikacyjnym.';
            this.$data.initiateError = null;
          } else if (response.data.initiateResult === 'rejected') {
            this.$data.initiateSuccess = null;
            this.$data.initiateError = 'Nie udało się zrealizować weryfikacji. Skontaktuj się z administracją serwisu.';
          }
        });
    },
    verify() {
      this.$data.verificationError = null;
      axios.post('/User/Verification/Verify', {
        phoneNumber: this.$data.phoneNumber,
        verificationCode: this.$data.verificationCode,
      }).then(response => {
        if (response.data.verificationResult === 'verified') {
          this.$data.userVerified = true;
        } else if (response.data.verificationResult === 'notVerified') {
          this.$data.verificationError = 'Weryfikacja niepomyślna. Upewnij się że wpisujesz poprawny kod bądź skontaktuj się z administracją serwisu.';
        }
      });
    },
  },
});

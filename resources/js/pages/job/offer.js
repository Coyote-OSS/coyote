import {mapGetters} from 'vuex';
import VueFlag from '../../components/flags/flag.vue';
import VueMap from '../../components/google-maps/map.vue';
import VueMarker from '../../components/google-maps/marker.vue';
import {default as mixins} from '../../components/mixins/user';
import store from '../../store';
import {createVueApp} from '../../vue';

createVueApp('Flags', '#js-flags', {
  delimiters: ['${', '}'],
  data: () => ({job: window.job}),
  components: {'vue-flag': VueFlag},
  store,
  created() {
    store.commit('flags/init', window.flags);
  },
  computed: {
    flags() {
      return store.getters['flags/filter'](this.job.id, 'Coyote\\Job').filter(flag => flag.resources.length === 1);
    },
  },
});

createVueApp('Map', '#map', {
  delimiters: ['${', '}'],
  components: {
    'vue-map': VueMap,
    'vue-marker': VueMarker,
  },
});

createVueApp('Sidemenu', '#js-sidemenu', {
  delimiters: ['${', '}'],
  data: () => ({job: window.job}),
  store,
  mixins: [mixins],
  created() {
    store.state.jobs.subscriptions = window.subscriptions;
  },
  methods: {
    subscribe() {
      store.dispatch('jobs/subscribe', this.job);
    },
  },
  computed: {
    ...mapGetters('jobs', ['isSubscribed']),
    ...mapGetters('user', ['isAuthorized']),
  },
});

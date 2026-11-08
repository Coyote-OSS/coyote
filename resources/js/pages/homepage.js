import axios from 'axios';
import PerfectScrollbar from 'perfect-scrollbar';
import {mapGetters} from "vuex";

import VueMicroblog from "../components/microblog/microblog.vue";
import store from "../store/index.ts";
import {createVueAppNotifications} from '../vue';
import {default as LiveMixin} from './microblog/live';

createVueAppNotifications('Microblog', '#js-microblog', {
  delimiters: ['${', '}'],
  mixins: [LiveMixin],
  components: {'vue-microblog': VueMicroblog},
  store,
  created() {
    Object.keys(window.microblogs).forEach(id => store.commit('microblogs/ADD', window.microblogs[id]));

    store.commit('flags/init', window.flags);
  },
  mounted() {
    this.liveNotifications();
  },
  computed: mapGetters('microblogs', ['microblogs']),
});

function switchForumTab(index) {
  axios.post('/User/Settings/Ajax', {'homepage_mode': index});
}

function switchReputationTab(index) {
  axios.post('/User/Settings/Ajax', {'homepage_reputation': index});
}

function voteUrl(type, id) {
  return type === 'post' ? `/Forum/Post/Vote/${id}` : `/Forum/Comment/Vote/${id}`;
}

function voteResult(type, data) {
  if (type === 'post') {
    return {score: data.users.length, voted: data.users.includes(store.state.user.user?.name)};
  }
  return {score: data.votes, voted: data.own_vote};
}

function initActivityVotes() {
  document.querySelectorAll('.home-activity__vote').forEach(button => {
    button.addEventListener('click', () => {
      const {voteType, voteId} = button.dataset;
      button.disabled = true;
      axios.post(voteUrl(voteType, voteId))
        .then(response => {
          const {score, voted} = voteResult(voteType, response.data);
          button.querySelector('.home-activity__score').textContent = score;
          button.classList.toggle('home-activity__vote--voted', voted);
        })
        .finally(() => button.disabled = false);
    });
  });
}

function initActivitiesShowMore() {
  const button = document.getElementById('home-activities-more');
  if (button) {
    button.addEventListener('click', () => {
      document.querySelector('.home-activities').classList.add('home-activities--unfolded');
      button.remove();
    });
  }
}

function initMembersDock() {
  const members = document.getElementById('home-members');
  const toggle = docked => {
    members.classList.toggle('home-members--docked', docked);
    axios.post('/User/Settings/Ajax', {'homepage_members': docked ? 'docked' : 'inline'});
  };
  document.getElementById('home-members-dock')?.addEventListener('click', () => toggle(true));
  document.getElementById('home-members-undock')?.addEventListener('click', () => toggle(false));
}

(function () {
  const legacyStream = document.getElementById('stream');
  if (legacyStream) {
    new PerfectScrollbar(legacyStream);
  }
  initActivityVotes();
  initMembersDock();
  initActivitiesShowMore();

  const forumTabs = document.querySelectorAll('#forum-tabs .nav-link');
  for (let i = 0; i < forumTabs.length; i++) {
    forumTabs[i].addEventListener('click', () => switchForumTab(i));
  }

  const reputationTabs = document.querySelectorAll('#reputation-tabs .nav-link');
  for (let i = 0; i < reputationTabs.length; i++) {
    reputationTabs[i].addEventListener('click', () => switchReputationTab(i));
  }
})();

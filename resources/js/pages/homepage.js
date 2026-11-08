import axios from 'axios';
import PerfectScrollbar from 'perfect-scrollbar';
import {createApp} from 'vue';
import {mapGetters} from "vuex";

import VuePmChat from '../components/pm/chat.vue';
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

function initMembersFollow() {
  document.querySelectorAll('.home-member__follow').forEach(button => {
    button.addEventListener('click', () => {
      const followed = !button.classList.contains('home-member__follow--followed');
      const name = button.closest('li').querySelector('.home-member__username').textContent.trim();
      button.disabled = true;
      axios.post(`/User/${followed ? 'Follow' : 'Unfollow'}/${button.dataset.followUserId}`)
        .then(() => {
          // the row deliberately stays where it is, so the click is easy to undo;
          // it's re-sorted on reload, or when the panel is pinned/unpinned
          button.classList.toggle('home-member__follow--followed', followed);
          button.setAttribute('aria-pressed', String(followed));
          button.title = followed
            ? `Obserwujesz ${name}. Obserwowani są wyświetlani na górze listy. Kliknij, aby przestać obserwować.`
            : `Obserwuj ${name}. Obserwowani są wyświetlani na górze listy.`;
          button.closest('li').dataset.followed = followed ? '1' : '0';
        })
        .finally(() => button.disabled = false);
    });
  });
}

/**
 * Followed users first, otherwise in their natural order (e.g. most recently seen first).
 * Afterwards, the first few of each group are listed, as when the page was loaded.
 */
function sortMembers() {
  const list = document.querySelector('.home-members__list');
  ['online', 'recent'].forEach(group => {
    const items = [...list.querySelectorAll(`li[data-member-group="${group}"]`)];
    if (items.length === 0) {
      return;
    }
    const before = items[0].previousElementSibling;
    items.sort((a, b) => (b.dataset.followed - a.dataset.followed) || (a.dataset.memberOrder - b.dataset.memberOrder));
    before.after(...items);

    const shown = Number(list.dataset[`${group}Shown`]);
    const allRevealed = !document.getElementById(`home-members-more-${group}`);
    items.forEach((item, index) => {
      item.classList.toggle('home-members__extra', index >= shown);
      item.classList.toggle(`home-members__extra--${group}`, index >= shown);
      if (allRevealed) {
        item.classList.add('home-members__extra--revealed');
      }
    });
  });
}

function initMembersDock() {
  const members = document.getElementById('home-members');
  const toggle = docked => {
    members.classList.toggle('home-members--docked', docked);
    sortMembers();
    axios.post('/User/Settings/Ajax', {'homepage_members': docked ? 'docked' : 'inline'});
  };
  document.getElementById('home-members-dock')?.addEventListener('click', () => toggle(true));
  document.getElementById('home-members-undock')?.addEventListener('click', () => toggle(false));
}

function initBlockedActivities() {
  document.querySelectorAll('.home-activity__blocked').forEach(button => {
    button.addEventListener('click', () => button.closest('.home-activity').classList.toggle('home-activity--revealed'));
  });
}

function initMembersShowMore() {
  const reveal = items => items.forEach(item => item.classList.add('home-members__extra--revealed'));
  const hidden = type => [...document.querySelectorAll(`.home-members__extra--${type}:not(.home-members__extra--revealed)`)];

  document.getElementById('home-members-more-online')?.addEventListener('click', event => {
    reveal(hidden('online'));
    event.currentTarget.closest('li').remove();
  });
  document.getElementById('home-members-more-recent')?.addEventListener('click', event => {
    const remaining = hidden('recent');
    reveal(remaining.slice(0, 10));
    if (remaining.length <= 10) {
      event.currentTarget.closest('li').remove();
    }
  });
}

/**
 * On desktop, private messages from the community list open in a chat box at the bottom
 * of the page; on mobile (and with modifier keys) the links go to the full conversation.
 */
function initPmChat() {
  const element = document.getElementById('js-pm-chat');
  if (!element || !window.matchMedia('(min-width: 768px)').matches) {
    return;
  }
  const app = createApp(VuePmChat);
  app.use(store);
  app.provide('icons', window.icons);
  const chat = app.mount(element);

  document.querySelectorAll('[data-chat-user-id]').forEach(link => {
    link.addEventListener('click', event => {
      if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
        return;
      }
      event.preventDefault();
      chat.open({
        id: Number(link.dataset.chatUserId),
        name: link.dataset.chatUserName,
        photo: link.dataset.chatUserPhoto || null,
        initials: link.dataset.chatUserInitials,
      });
    });
  });
}

(function () {
  initPmChat();
  initBlockedActivities();
  initMembersShowMore();
  initMembersFollow();
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

import axios from 'axios';

const state = {
  user: window["__INITIAL_STATE"]?.user || {},
  followers: window["__INITIAL_STATE"]?.followers || [],
};

export const guest = !('name' in window['__INITIAL_STATE'].user);

const getters = {
  isAuthorized: state => state.user.id !== undefined,
  dateFormat: state => (defaultFormat = null) => state.user.date_format ?? defaultFormat,
  isBlocked: state => (userId: number) => state.followers.find(follower => follower.user_id === userId && follower.is_blocked),
  follows: state => (userId: number) => state.followers.find(follower => follower.user_id === userId && !follower.is_blocked),
  followers: state => state.followers.find(follower => !follower.is_blocked),
  confirmedMail: state => state.user.confirmedMail,
  needsPhoneVerification: state => state.user.needsPhoneVerification,
};

const mutations = {
  update(state, payload) {
    state.user = Object.assign(state.user, payload);
  },

  ADD_RELATION(state, {userId, isBlocked}) {
    state.followers.push({is_blocked: isBlocked, user_id: userId});
  },

  REMOVE_RELATION(state, {userId, isBlocked}) {
    const index = state.followers
      .findIndex(follower => follower.user_id === userId && follower.is_blocked === isBlocked);
    if (index === -1) {
      throw new Error('Failed to remove relation of userId: ' + userId);
    }
    state.followers.splice(index, 1);
  },

  changePostStyle(state, style): void {
    state.user = Object.assign(state.user, {postCommentStyle: style});
  },
};

const actions = {
  block({commit}, relatedUserId: number) {
    return axios.post(`/User/Block/${relatedUserId}`).then(() => commit('ADD_RELATION', {userId: relatedUserId, isBlocked: true}));
  },
  unblock({commit}, relatedUserId: number) {
    return axios.post(`/User/Unblock/${relatedUserId}`).then(() => commit('REMOVE_RELATION', {userId: relatedUserId, isBlocked: true}));
  },
  follow({commit}, relatedUserId: number) {
    return axios.post(`/User/Follow/${relatedUserId}`).then(() => commit('ADD_RELATION', {userId: relatedUserId, isBlocked: false}));
  },
  unfollow({commit}, relatedUserId: number) {
    return axios.post(`/User/Unfollow/${relatedUserId}`)
      .then(() => commit('REMOVE_RELATION', {userId: relatedUserId, isBlocked: false}));
  },
  pushSubscription({commit}, pushSubscription) {
    return axios.post('/User/push', pushSubscription);
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};

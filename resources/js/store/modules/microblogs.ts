import axios from "axios";

import {Microblog, Paginator, Tag, User} from "../../types/models";

type ParentChild = {parent: Microblog, comment: Microblog};

const state: Paginator = {
  current_page: 0,
  data: [],
  from: 0,
  last_page: 0,
  path: "",
  per_page: 0,
  to: 0,
  total: 0,
};

const getters = {
  microblogs: (state, getters, rootState) => {
    const sorted = Object.values(state.data).sort((a, b) => (b as Microblog).id! - (a as Microblog).id!);
    const sponsoredIndex = sorted.findIndex(microblog => (microblog as Microblog).is_sponsored);
    if (!rootState.user.is_sponsor && sponsoredIndex > -1) {
      const sponsored = sorted[sponsoredIndex];
      const random = (min: number, max: number) => Math.floor(Math.random() * (max - min) + min);
      const randomIndex = random(1, 4);

      if (sponsoredIndex > randomIndex) {
        sorted.splice(sponsoredIndex, 1);
        sorted.splice(randomIndex, 0, sponsored);
      }
    }
    return sorted;
  },

  exists: state => (id: number) => id in state.data,
  currentPage: state => state.current_page,
  totalPages: state => state.last_page,
};

const mutations = {
  INIT(state, pagination: Paginator) {
    state = Object.assign(state, pagination);
  },

  ADD(state, microblog: Microblog) {
    state.data[microblog.id!] = microblog;
  },

  UPDATE(state, microblog: Microblog) {
    let {text, html, assets} = microblog; // update only text and html version

    state.data[microblog.id!] = {...state.data[microblog.id!], ...{text, html, assets}};
  },

  DELETE(state, microblog: Microblog) {
    delete state.data[microblog.id!];
  },

  RESTORE(state, microblog: Microblog) {
    microblog.deleted_at = null;
  },

  ADD_COMMENT(state, {parent, comment}: ParentChild) {
    if (Array.isArray(parent.comments)) {
      parent.comments = {};
    }
    parent.comments[comment.id!] = comment;
    parent.comments_count! += 1;
  },

  UPDATE_COMMENT(state, {parent, comment}: ParentChild) {
    let {text, html} = comment; // update only text and html version
    parent.comments[comment.id!] = {...parent.comments[comment.id!], ...{text, html}};
  },

  DELETE_COMMENT(state, comment: Microblog) {
    delete state.data[comment.parent_id!].comments[comment.id!];
    state.data[comment.parent_id!].comments_count -= 1;
  },

  RESTORE_COMMENT(state, comment: Microblog) {
    comment.deleted_at = null;
    state.data[comment.parent_id!].comments_count += 1;
  },

  VOTE(state, microblog: Microblog) {
    microblog.is_voted = !microblog.is_voted;
    microblog.votes += (microblog.is_voted ? 1 : -1);
  },

  SET_COMMENTS(state, {microblog, comments}) {
    microblog.comments = comments;
    microblog.comments_count = Object.keys(comments).length;
  },

  UPDATE_VOTERS(state, {microblog, users, user}: {microblog: Microblog, users: string[], user?: User}) {
    microblog.voters = users;
    microblog.votes = users.length;
    microblog.is_voted = users.includes(<string>user?.name);
  },

  TOGGLE_TAG(state, {microblog, tag}: {microblog: Microblog, tag: Tag}) {
    const index = microblog.tags!.findIndex(item => item.name === tag.name);

    index > -1 ? microblog.tags!.splice(index, 1) : microblog.tags!.push(tag);
  },

  TOGGLE_SPONSORED(state, microblog: Microblog) {
    microblog.is_sponsored = !microblog.is_sponsored;
  },

  TOGGLE_EDIT(state, microblog: Microblog) {
    microblog.is_editing = !microblog.is_editing;
  },

  TOGGLE_SUBSCRIBED(state, microblog: Microblog) {
    microblog.is_subscribed = !microblog.is_subscribed;
  },
};

const actions = {
  subscribe({commit}, microblog: Microblog) {
    commit('TOGGLE_SUBSCRIBED', microblog);

    axios.post(`/Mikroblogi/Subscribe/${microblog.id}`);
  },

  delete({commit}, microblog: Microblog) {
    return axios.delete(`/Mikroblogi/Delete/${microblog.id}`).then(() => commit('DELETE', microblog));
  },

  restore({commit}, microblog: Microblog) {
    return axios.post(`/Mikroblogi/Restore/${microblog.id}`).then(() => commit('RESTORE', microblog));
  },

  deleteComment({commit}, microblog: Microblog) {
    return axios.delete(`/Mikroblogi/Comment/Delete/${microblog.id}`).then(() => commit('DELETE_COMMENT', microblog));
  },

  restoreComment({commit}, comment: Microblog) {
    return axios.post(`/Mikroblogi/Restore/${comment.id}`).then(() => commit('RESTORE_COMMENT', comment));
  },

  save({commit, getters}, microblog: Microblog) {
    return axios.post<Microblog, any>(`/Mikroblogi/Edit/${microblog.id || ''}`, microblog).then(result => {
      commit(getters.exists(result.data.id) ? 'UPDATE' : 'ADD', result.data);

      return result;
    });
  },

  saveComment({state, commit, getters}, comment: Microblog) {
    return axios.post<any>(`/Mikroblogi/Comment/${comment.id || ''}`, comment).then(response => {
      const comment = response.data.data;
      const parent = state.data[comment.parent_id!];

      if (parent.comments[comment.id!]) {
        commit('UPDATE_COMMENT', {parent, comment});
      } else {
        commit('ADD_COMMENT', {parent, comment});

        if (response.data.is_subscribed && !parent.is_subscribed) {
          commit('TOGGLE_SUBSCRIBED', parent);
        }
      }

      return response;
    });
  },

  loadComments({commit}, microblog: Microblog) {
    return axios.get(`/Mikroblogi/Comment/Show/${microblog.id}`).then(response => commit('SET_COMMENTS', {
      microblog,
      comments: response.data,
    }));
  },

  loadVoters({commit, dispatch}, microblog: Microblog) {
    if (!microblog.votes) {
      return;
    }

    return axios.get<any>(`/Mikroblogi/Voters/${microblog.id}`).then(response => {
      dispatch('updateVoters', {microblog, users: response.data.users});
    });
  },

  updateVoters({commit, rootState}, {microblog, users}: {microblog: Microblog, users: string[]}) {
    commit('UPDATE_VOTERS', {microblog, users, user: rootState.user.user});
  },

  vote({commit, dispatch}, microblog: Microblog) {
    commit('VOTE', microblog);

    return axios.post<any>(`/Mikroblogi/Vote/${microblog.id}`)
      .then(response => dispatch('updateVoters', {microblog, users: response.data.users}))
      .catch(() => commit('VOTE', microblog));
  },

  toggleSponsored({commit}, microblog: Microblog) {
    return axios.post(`/Mikroblogi/Sponsored/${microblog.id}`).then(() => commit('TOGGLE_SPONSORED', microblog));
  },

  hit({commit}, microblog: Microblog) {
    return navigator.sendBeacon(`/Mikroblogi/Hit/${microblog.id}`);
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};

import axios from "axios";
import {Comment} from '../../types/models';

const state = {comments: {}};

const mutations = {
  INIT(state, comments) {
    state.comments = comments;
  },
  UPDATE(state, comment: Comment) {
    if (comment.parent_id) {
      const parent = state.comments[comment.parent_id];
      if (Array.isArray(parent.children)) {
        parent.children = {};
      }
      parent.children[comment.id] = comment;
    } else {
      state.comments[comment.id] = comment;
    }
  },
  DELETE(state, comment) {
    if (comment.parent_id) {
      delete state.comments[comment.parent_id].children[comment.id];
    } else {
      delete state.comments[comment.id];
    }
  },
};
const actions = {
  save({commit}, comment) {
    return axios.post(`/Comment/${comment.id || ''}`, comment).then(response => (commit('UPDATE', response.data), response));
  },
  delete({commit}, comment) {
    return axios.delete(`/Comment/${comment.id}`).then(() => commit('DELETE', comment));
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};

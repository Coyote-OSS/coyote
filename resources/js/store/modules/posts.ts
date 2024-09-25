import axios from "axios";
import {Forum, Paginator, Post, PostComment, PostLog, Topic, User} from "../../types/models";

type ParentChild = { post: Post, comment: PostComment };

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
  posts: state => Object.values(state.data).sort((a, b) => ((a as Post).created_at! > (b as Post).created_at!) ? 1 : -1),
  exists: state => (id: number) => id in state.data,
  currentPage: state => state.current_page,
  totalPages: state => state.last_page,
};

const mutations = {
  init(state, pagination) {
    state = Object.assign(state, pagination);
  },

  add(state, post: Post) {
    state.data[post.id!] = post;
  },

  update(state, post: Post) {
    const {text, html, assets, editor, updated_at, edit_count, score} = post;
    state.data[post.id] = {...state.data[post.id], ...{text, html, assets, editor, updated_at, edit_count, score}};
  },

  delete(state, post: Post) {
    post.deleted_at = new Date();
  },

  edit(state, editable: Post | PostComment) {
    editable.is_editing = !editable.is_editing;
  },

  addComment(state, {post, comment}: ParentChild) {
    if (Array.isArray(post.comments)) {
      post.comments = {};
    }
    post.comments[comment.id!] = comment;
    post.comments_count! += 1;
  },

  updateComment(state, {post, comment}: ParentChild) {
    let {text, html} = comment; // update only text and html version

    post.comments[comment.id] = {...post.comments[comment.id], ...{text, html}};
  },

  deleteComment(state, comment: PostComment) {
    const post = state.data[comment.post_id];
    delete post.comments[comment.id!];
    post.comments_count! -= 1;
  },

  setComments(state, {post, comments}) {
    post.comments = comments;
    post.comments_count = comments.length;
  },

  restore(state, post: Post) {
    post.deleted_at = null;

    delete post.delete_reason;
    delete post.deleter_name;
  },

  vote(state, post: Post) {
    if (post.is_voted) {
      post.is_voted = false;
      post.score -= 1;
    } else {
      post.is_voted = true;
      post.score += 1;
    }
  },

  accept(state, post: Post) {
    if (post.is_accepted) {
      post.is_accepted = false;
    } else {
      let values: Post[] = Object.values(state.data);

      // user choose different option
      for (let item of values) {
        if (item.is_accepted) {
          item.is_accepted = false;
        }
      }

      post.is_accepted = true;
    }
  },

  subscribe(state, post: Post) {
    post.is_subscribed = true;
  },

  unsubscribe(state, post: Post) {
    post.is_subscribed = false;
  },

  updateVoters(state, {post, users, user}: { post: Post, users: string[], user?: User }) {
    post.voters = users;
    post.score = users.length;
    post.is_voted = users.includes(<string>user?.name);
  },
};

function savePostUrl(forum: Forum, topic: Topic, post: Post) {
  if (post.id) {
    return `/Forum/${forum.slug}/Submit/${topic?.id || ''}/${post.id}`;
  }
  return `/Forum/${forum.slug}/Submit/${topic?.id || ''}`;
}

const actions = {
  vote({commit, dispatch}, post: Post) {
    commit('vote', post);

    return axios.post<any>(`/Forum/Post/Vote/${post.id}`)
      .then(response => dispatch('updateVoters', {post, users: response.data.users}))
      .catch(() => commit('vote', post));
  },

  accept({commit, getters}, post: Post) {
    commit('accept', post);

    return axios.post(`/Forum/Post/Accept/${post.id}`).catch(() => commit('accept', post));
  },

  subscribe({commit}, post: Post) {
    const subscribe = () => commit(post.is_subscribed ? 'unsubscribe' : 'subscribe', post);
    subscribe();

    return axios.post(`/Forum/Post/Subscribe/${post.id}`).catch(subscribe);
  },

  save({commit, state, getters, rootState, rootGetters}, post: Post) {
    const topic = rootGetters['topics/topic'];
    const forum = rootGetters['forums/forum'];

    const payload = {
      text: post.text,
      title: topic.title,
      is_sticky: topic.is_sticky,
      assets: post.assets,
      tags: topic.tags!.map(o => o['name']),
      poll: rootState.poll.poll,
    };
    return axios.post<any>(savePostUrl(forum, topic, post), payload).then(result => {
      commit(getters.exists(result.data.id) ? 'update' : 'add', result.data);
      return result;
    });
  },

  saveComment({state, commit, getters}, comment: PostComment) {
    return axios.post<any>(`/Forum/Comment/${comment.id || ''}`, comment).then(result => {
      const post = state.data[result.data.data.post_id!];

      commit(result.data.is_subscribed ? 'subscribe' : 'unsubscribe', post);
      commit(post.comments[result.data.data.id!] ? 'updateComment' : 'addComment', {post, comment: result.data.data});
    });
  },

  delete({commit}, {post, reasonId}: { post: Post, reasonId: number | null }) {
    return axios.delete(`/Forum/Post/Delete/${post.id}`, {data: {reason: reasonId}}).then(() => commit('delete', post));
  },

  deleteComment({commit}, comment: PostComment) {
    return axios.delete(`/Forum/Comment/Delete/${comment.id}`).then(() => commit('deleteComment', comment));
  },

  migrateComment({commit}, comment: PostComment) {
    return axios.post(`/Forum/Comment/Migrate/${comment.id}`).then(response => {
      commit('deleteComment', comment);

      return response;
    });
  },

  restore({commit}, post: Post) {
    return axios.post(`/Forum/Post/Restore/${post.id}`).then(() => commit('restore', post));
  },

  merge({commit, getters}, post: Post) {
    return axios.post(`/Forum/Post/Merge/${post.id}`).then(result => {
      commit('delete', post);
      commit('update', result.data);
    });
  },

  rollback({commit}, log: PostLog) {
    return axios.post<{ url: string }>(`/Forum/Post/Rollback/${log.post_id}/${log.id}`);
  },

  loadComments({commit}, post: Post) {
    axios.get(`/Forum/Comment/Show/${post.id}`).then(result => {
      commit('setComments', {post, comments: result.data});
    });
  },

  changePage({commit, rootGetters}, page: number) {
    const topic = rootGetters['topics/topic'];
    const forum = rootGetters['forums/forum'];

    return axios.get(`/Forum/${forum.slug}/${topic.id}-${topic.slug}`, {params: {page}}).then(result => {
      commit('init', result.data);

      return result;
    });
  },

  loadVoters({dispatch}, post: Post) {
    if (!post.score) {
      return;
    }

    return axios.get<any>(`/Forum/Post/Voters/${post.id}`).then(response => {
      dispatch('updateVoters', {post, users: response.data.users});
    });
  },

  updateVoters({commit, rootState}, {post, users}: { post: Post, users: string[] }) {
    commit('updateVoters', {post, users, user: rootState.user.user});
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};

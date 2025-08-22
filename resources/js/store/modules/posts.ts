import axios from "axios";
import {TreeMap} from "../../treeTopic/treeMap";
import {postOrdering, Sorter, TreeOrderBy} from "../../treeTopic/treeOrderBy";
import {TreeItem, TreeTopicRecords} from "../../treeTopic/treeTopicRecords";
import {Forum, Paginator, Post, PostComment, PostLog, SubTreeItem, Topic, TreePost, TreePostItem, User} from "../../types/models";
import {TreeTopicOrder} from "./topics";

type ParentChild = {
  postId: number,
  comment: PostComment
};

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

const flatTreeItem: TreePostItem = {
  indent: 0,
  level: 1,
  linksToParent: false,
  parentLevels: [],
  linksToChildren: false,
  hasDeeperChildren: false,
  childrenAuthors: [],
};

type Function<A, B> = (argument: A) => B;

function topicOrderToTreeOrdering(topicOrder: TreeTopicOrder): TreeOrderBy {
  const map = {
    'byScore': 'orderByMostLikes',
    'newest': 'orderByCreationDateNewest',
    'oldest': 'orderByCreationDateOldest',
  };
  return map[topicOrder];
}

const getters = {
  posts(state): Post[] {
    const posts: Post[] = Object.values(state.data);
    posts.sort((a, b) => a.id > b.id ? 1 : -1); // this mutates state! Ugh!
    return posts;
  },
  linearTopicPosts(state, getters): Post[] {
    return getters.posts;
  },
  treeTopicPostsFirst(state, getters): Post {
    return getters.treeTopicRoot;
  },
  treeTopicRoot(state, getters): Post {
    return getters.posts[0];
  },
  treeTopicPostTargetId(state, getters, rootState, rootGetters): Function<number, number|null> {
    return (postId: number): number|null => {
      const post: Post = state.data[postId];
      const topic: Topic = rootGetters["topics/topic"];
      if (post.parentPostId != topic.first_post_id) {
        return post.parentPostId;
      }
      return null;
    };
  },
  treeTopicPostsRemaining(state, getters): TreePost[] {
    if (getters.isLinearized) {
      return getters.posts.slice(1).map(post => ({post, treeItem: flatTreeItem}));
    }

    const treeMap: Map<number, SubTreeItem> = getters.treeTopicPostsMap;

    function parentLevelsWithSiblings(post: Post): number[] {
      const parentLevels: number[] = [];
      let nextPostId: number|null = post.id;
      let nextLevel = 0;
      while (nextPostId && treeMap.has(nextPostId)) {
        const nextPost: SubTreeItem = treeMap.get(nextPostId)!;
        if (nextPost.hasNextSibling) {
          parentLevels.push(nextLevel);
        }
        ++nextLevel;
        nextPostId = nextPost.post.parentPostId;
      }
      return parentLevels;
    }

    function postAnswersAuthors(postId: number): User[] {
      const posts: Post[] = Object.values(state.data);
      const postsTree = new TreeMap<number, Post>();
      for (const post of posts) {
        postsTree.put(post.id, post, post.parentPostId || undefined);
      }
      return postsTree.childrenOf(postId).map(post => post.user!);
    }

    const subTreeDepth = 9;
    const from: SubTreeItem[] = Array.from(treeMap.values());
    return from
      .map(function (subtreeItem: SubTreeItem): TreePost {
        const nestLevel = subtreeItem.nestLevel - subtreeItem.subtreeNestLevel;
        const indent = nestLevel - 1;
        return {
          post: subtreeItem.post,
          treeItem: {
            indent,
            level: subtreeItem.nestLevel,
            linksToParent: indent > 0,
            parentLevels: parentLevelsWithSiblings(subtreeItem.post)
              .filter(parentLevel => (nestLevel - parentLevel - 1) > 0),
            linksToChildren: subtreeItem.hasChildren,
            hasDeeperChildren: subtreeItem.hasChildren,
            childrenAuthors: postAnswersAuthors(subtreeItem.post.id),
          },
        };
      })
      .filter((treePost: TreePost): boolean => treePost.treeItem.indent < subTreeDepth)
      .map((treePost: TreePost): TreePost => {
        if (treePost.treeItem.indent === subTreeDepth - 1) {
          return {
            post: treePost.post,
            treeItem: {
              indent: treePost.treeItem.indent,
              level: treePost.treeItem.level,
              linksToParent: treePost.treeItem.linksToParent,
              parentLevels: treePost.treeItem.parentLevels,
              linksToChildren: false,
              hasDeeperChildren: treePost.treeItem.hasDeeperChildren,
              childrenAuthors: treePost.treeItem.childrenAuthors,
            },
          };
        }
        return {
          post: treePost.post,
          treeItem: {
            indent: treePost.treeItem.indent,
            level: treePost.treeItem.level,
            linksToParent: treePost.treeItem.linksToParent,
            parentLevels: treePost.treeItem.parentLevels,
            linksToChildren: treePost.treeItem.linksToChildren,
            hasDeeperChildren: false,
            childrenAuthors: treePost.treeItem.childrenAuthors,
          },
        };
      });
  },
  treeTopicPostsMap(state, getters): Map<number, SubTreeItem> {
    const map = new Map<number, SubTreeItem>();
    const posts = getters.posts;
    const sorter = getters.postOrdering;
    const tree = new TreeTopicRecords<Post>(sorter);
    const postsWithChildren = new Set();
    for (const post of posts) {
      if (!post.parentPostId) {
        tree.setRoot(post.id, post);
      } else {
        tree.addChild(post.id, post.parentPostId, post, post.childrenFolded);
        postsWithChildren.add(post.parentPostId);
      }
    }
    const flatTreeItems: TreeItem<Post>[] = tree.flatTreeItemsChildrenOf(getters.treeSelectedSubtreePostId);
    let parentNestLevel: number|null = null;
    for (const item of flatTreeItems) {
      const post: Post = item.item;
      if (post.parentPostId !== null) {
        if (parentNestLevel === null) {
          parentNestLevel = item.nestLevel - 1;
        }
        map.set(post.id, {
          post: post,
          nestLevel: item.nestLevel,
          subtreeNestLevel: parentNestLevel,
          hasNextSibling: item.hasNextSibling,
          hasChildren: postsWithChildren.has(post.id),
        });
      }
    }
    return map;
  },
  treeSelectedSubtreePostId(state, getters, rootState, rootGetters): number {
    return rootGetters['topics/treeTopicSelectedSubtreePostId'];
  },
  postOrdering(state, getters, rootState, rootGetters): Sorter {
    return postOrdering(topicOrderToTreeOrdering(rootGetters['topics/treeTopicOrder']));
  },
  isLinearized(state, getters, rootState, rootGetters): boolean {
    return rootGetters['topics/treeTopicOrder'] === 'linear';
  },
  exists(state) {
    return (postId: number): boolean => {
      return postId in state.data;
    };
  },
  currentPage(state): number {
    return state.current_page;
  },

  totalPages(state): number {
    return state.last_page;
  },
  isLastPage(state, getters): boolean {
    return getters.currentPage >= getters.totalPages;
  },
  commentExists(state) {
    return (postId: number, postCommentId: number): boolean => {
      return postCommentId in state.data[postId].comments;
    };
  },
  commentIsEditing(state) {
    return (postId: number, postCommentId: number): boolean => {
      return state.data[postId].comments[postCommentId].is_editing;
    };
  },
};

const mutations = {
  init(state, pagination): void {
    state = Object.assign(state, pagination);
  },
  add(state, post: Post): void {
    state.data[post.id!] = post;
  },
  update(state, post: Post): void {
    const {text, html, assets, editor, updated_at, edit_count, score} = post;
    state.data[post.id] = {...state.data[post.id], ...{text, html, assets, editor, updated_at, edit_count, score}};
  },
  delete(state, post: Post): void {
    post.deleted_at = new Date();
  },
  editStart(state, editable: Post|PostComment): void {
    editable.is_editing = true;
  },
  editEnd(state, editable: Post|PostComment): void {
    editable.is_editing = false;
  },
  addComment(state, {postId, comment}: ParentChild): void {
    const post = state.data[postId];
    if (Array.isArray(post.comments)) {
      post.comments = {};
    }
    post.comments[comment.id!] = comment;
    post.comments_count! += 1;
  },
  updateComment(state, {postId, comment}: ParentChild): void {
    const post = state.data[postId];
    post.comments[comment.id] = {
      ...post.comments[comment.id],
      text: comment.text, // update only text and html version
      html: comment.html,
    };
  },
  deleteComment(state, comment: PostComment): void {
    const post = state.data[comment.post_id];
    delete post.comments[comment.id!];
    post.comments_count! -= 1;
  },
  setComments(state, {post, comments}): void {
    post.comments = comments;
    post.comments_count = comments.length;
  },
  restore(state, post: Post): void {
    post.deleted_at = null;

    delete post.delete_reason;
    delete post.deleter_name;
  },
  vote(state, post: Post): void {
    if (post.is_voted) {
      post.is_voted = false;
      post.score -= 1;
    } else {
      post.is_voted = true;
      post.score += 1;
    }
  },
  accept(state, post: Post): void {
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
  subscribe(state, postId: number): void {
    state.data[postId].is_subscribed = true;
  },
  unsubscribe(state, postId: number): void {
    state.data[postId].is_subscribed = false;
  },
  updateVoters(state, {postId, users, user}: { postId: number, users: string[], user?: User }): void {
    state.data[postId].voters = users;
    state.data[postId].score = users.length;
    state.data[postId].is_voted = users.includes(user?.name!);
  },
  foldChildren(state, post: Post): void {
    post.childrenFolded = true;
  },
  unfoldChildren(state, post: Post): void {
    post.childrenFolded = false;
  },
};

function savePostUrl(forum: Forum, topic: Topic, post: Post): string {
  if (post.id) {
    return `/Forum/${forum.slug}/Submit/${topic?.id || ''}/${post.id}`;
  }
  return `/Forum/${forum.slug}/Submit/${topic?.id || ''}`;
}

const actions = {
  vote({commit, dispatch}, post: Post) {
    commit('vote', post);

    return axios.post<any>(`/Forum/Post/Vote/${post.id}`)
      .then(response => dispatch('updateVoters', {postId: post.id, users: response.data.users}))
      .catch(() => commit('vote', post));
  },

  accept({commit, getters}, post: Post) {
    commit('accept', post);

    return axios.post(`/Forum/Post/Accept/${post.id}`).catch(() => commit('accept', post));
  },

  subscribe({commit}, post: Post) {
    commit('subscribe', post.id);
    return axios.post(`/Forum/Post/Subscribe/${post.id}`).catch(() => commit('unsubscribe', post.id));
  },
  unsubscribe({commit}, post: Post) {
    commit('unsubscribe', post.id);
    return axios.post(`/Forum/Post/Subscribe/${post.id}`).catch(() => commit('subscribe', post.id));
  },
  savePostTreeAnswer(
    {commit, getters, rootState, rootGetters},
    [post, treeAnswerPostId]: [Post, number?],
  ) {
    const topic: Topic = rootGetters['topics/topic'];
    const forum: Forum = rootGetters['forums/forum'];

    const payload = {
      text: post.text,
      title: topic.title,
      is_sticky: topic.is_sticky,
      assets: post.assets,
      tags: topic.tags!.map(o => o['name']),
      poll: rootState.poll.poll,
      discussMode: topic.discuss_mode,
      treeAnswerPostId: treeAnswerPostId,
    };
    return axios.post<any>(savePostUrl(forum, topic, post), payload).then(response => {
      commit(getters.exists(response.data.id) ? 'update' : 'add', response.data);
      return response;
    });
  },

  saveComment({commit, getters}, comment: PostComment) {
    return axios.post<any>(`/Forum/Comment/${comment.id || ''}`, comment).then(response => {
      const comment: PostComment = response.data.data;
      const commentId = comment.id;
      const postId = comment.post_id;

      if (response.data.is_subscribed) {
        commit('subscribe', postId);
      } else {
        commit('unsubscribe', postId);
      }
      if (getters.commentExists(postId, commentId)) {
        commit('updateComment', {postId, comment});
      } else {
        commit('addComment', {postId, comment});
      }
    });
  },

  delete({commit}, {post, reasonId}: { post: Post, reasonId: number|null }) {
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
    return axios.post(`/Forum/Post/Merge/${post.id}`).then(response => {
      commit('delete', post);
      commit('update', response.data);
    });
  },

  rollback({commit}, log: PostLog) {
    return axios.post<{ url: string }>(`/Forum/Post/Rollback/${log.post_id}/${log.id}`);
  },

  loadComments({commit}, post: Post) {
    axios.get(`/Forum/Comment/Show/${post.id}`).then(response => {
      commit('setComments', {post, comments: response.data});
    });
  },

  changePage({commit, rootGetters}, page: number) {
    const topic = rootGetters['topics/topic'];
    const forum = rootGetters['forums/forum'];

    return axios.get(`/Forum/${forum.slug}/${topic.id}-${topic.slug}`, {params: {page}}).then(response => {
      commit('init', response.data);
      return response;
    });
  },

  loadVoters({dispatch}, post: Post) {
    if (!post.score) {
      return;
    }
    return axios.get<any>(`/Forum/Post/Voters/${post.id}`).then(response => {
      dispatch('updateVoters', {postId: post.id, users: response.data.users});
    });
  },

  updateVoters({commit, rootState}, {postId, users}: { postId: number, users: string[] }) {
    commit('updateVoters', {postId, users, user: rootState.user.user});
  },

  foldChildrenOfLevel({commit, getters}, level: number): void {
    const treePosts: TreePost[] = getters.treeTopicPostsRemaining;
    for (const treePost of treePosts) {
      if (treePost.treeItem.level === level) {
        commit('foldChildren', treePost.post);
      }
    }
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};

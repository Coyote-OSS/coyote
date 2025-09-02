<template>
  <div style="margin-left:56px;" v-if="isLinearized && hasPostTreeTarget" class="small text-muted">
    <vue-icon name="treeTopicPostTarget"/>
    Odpowiedź na #{{postTreeTargetId}}
  </div>
  <div class="position-relative" :class="[postIndentCssClasses, {'tree-post':is_mode_tree}]">
    <vue-post-guiderail
      v-if="guiderailVisible"
      :links-to-parent="linksToParent"
      :link-to-child="linkToChild"
      :parent-levels="parentLevels"
      :expanded="childrenFolded"
      @toggle="guiderailToggle"
    />
    <div class="d-flex">
      <div>
        <div class="neon-avatar-border me-1" v-if="post.user && is_mode_tree">
          <vue-avatar
            :name="post.user.name"
            :photo="post.user.photo"
            :initials="post.user.initials"
            :is-online="post.user.is_online"
            style="width:40px; z-index:1;"/>
        </div>
      </div>
      <div :id="anchor" class="position-relative" :style="{top:'-'+navigationBarOffset+'px'}"/>
      <div class="neon-tile neon-rounded card-post flex-grow-1 mb-0"
           :style="{minWidth:0}"
           :class="{'is-deleted': hidden, 'not-read': !post.is_read, 'highlight-flash': highlight, 'post-deleted-collapsed': isCollapsed}">
        <div v-if="post.deleted_at"
             @click="toggleDeletedPost"
             class="post-delete card-body text-decoration-none"
             :class="{'cursor-pointer':!postObscured}">
          <div class="mb-3">
            <strong>Usunięto</strong>
          </div>
          <vue-icon name="postDeleted"/>
          Post usunięty
          <vue-timeago :datetime="post.deleted_at"/>
          <template v-if="post.deleter_name">
            {{' '}}
            przez {{post.deleter_name}}.
          </template>
          <template v-else>.</template>
          <template v-if="post.delete_reason">
            {{' '}}
            Powód: {{post.delete_reason}}.
          </template>
        </div>
        <div v-else-if="authorBlocked" class="post-delete card-body text-decoration-none" @click="isCollapsed = !isCollapsed">
          <vue-icon name="postAuthorBlocked"/>
          Treść posta została ukryta, ponieważ autorem jest zablokowany przez Ciebie użytkownik.
        </div>
        <div :class="{'collapse': isCollapsed, 'd-lg-block': !isCollapsed && is_mode_linear}" class="d-none p-2 backport-post-header">
          <div class="row">
            <div class="col-2 pe-0">
              <div class="d-flex align-items-center">
                <span class="mb-0 post-author ms-2">
                  <vue-username v-if="post.user" :user="post.user"/>
                  <span v-else v-text="post.user_name"/>
                </span>
                <vue-user-status-banned :user="post.user" :is-author="post.user_id === topic.owner_id"/>
              </div>
            </div>
            <div class="col-10 text-truncate small">
              <div class="d-flex">
                <div style="font-size:12px;">
                  <vue-icon v-if="post.is_read" name="postWasRead"/>
                  <i v-else class="not-read" title="Nowy post"/>
                  {{' '}}
                  <a :href="post.url">
                    <vue-timeago :datetime="post.created_at"/>
                  </a>
                  <template v-if="is_mode_linear">
                    {{' '}}
                    <a v-if="post.clientIp" :href="post.clientIpAdminHref" class="text-muted">
                      {{post.clientIp}}
                    </a>
                    {{' '}}
                    <a v-if="post.browsingDevice" :href="post.browsingDeviceAdminHref" :title="post.userAgent" class="text-muted">
                      {{formatBrowsingDevice}}
                    </a>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div :class="{'collapse': isCollapsed}" class="card-body">
          <div class="media mb-2" :class="{'d-lg-none':is_mode_linear}">
            <div class="media-left me-2" v-if="is_mode_linear">
              <div class="neon-avatar-border" v-if="post.user">
                <vue-avatar
                  :name="post.user.name"
                  :photo="post.user.photo"
                  :is-online="post.user.is_online"
                  :initials="post.user.initials"
                  class="i-35"/>
              </div>
            </div>

            <div class="media-body">
              <span class="mb-0 post-author me-2">
                <vue-username v-if="post.user" :user="post.user"/>
                <span v-else>{{post.user_name}}</span>
                <vue-user-status-banned class="ms-2" :user="post.user" :is-author="post.user_id === topic.owner_id"/>
              </span>
              <a :href="post.url" class="text-muted small">
                <vue-timeago :datetime="post.created_at"/>
              </a>
              <a v-if="post.clientIp" :href="post.clientIpAdminHref" class="text-muted small">
                ({{post.clientIp}})
              </a>
              <span class="ms-1" v-if="post.edit_count && is_mode_tree"
                    :title="'edytowany ' + post.edit_count + 'x, ostatnio przez ' + post.editor.name + ', ' + editedTimeAgo">
                (edytowany)
              </span>
            </div>
          </div>

          <div class="row">
            <div class="d-none" :class="{'d-lg-block col-lg-2':is_mode_linear}" v-if="is_mode_linear">
              <template v-if="post.user">
                <div class="d-flex">
                  <div class="neon-avatar-border">
                    <vue-avatar
                      v-if="post.user"
                      :name="post.user.name"
                      :photo="post.user.photo"
                      :initials="post.user.initials"
                      :is-online="post.user.is_online"
                      class="i-70"/>
                  </div>
                </div>
                <span v-if="post.user.group_name && !is_mode_tree" class="badge badge-secondary mb-1">
                  {{post.user.group_name}}
                </span>
                <ul class="post-stats list-unstyled">
                  <li v-if="is_mode_linear">
                    <strong>Rejestracja:</strong>
                    <small>{{formatDistanceToNow(post.user.created_at)}}</small>
                  </li>

                  <li v-if="is_mode_linear">
                    <strong>Ostatnio:</strong>
                    <small>
                      {{formatDistanceToNow(post.user.visited_at ? post.user.visited_at : post.user.created_at)}}
                    </small>
                  </li>

                  <li v-if="is_mode_linear && post.user.location">
                    <strong>Lokalizacja:</strong>
                    <small>{{post.user.location}}</small>
                  </li>

                  <li v-if="is_mode_linear && post.user.allow_count">
                    <strong>Postów:</strong>
                    <small>
                      <a title="Znajdź posty tego użytkownika"
                         :href="`/Forum/User/${post.user.id}`"
                         style="text-decoration: underline">{{post.user.posts}}</a>
                    </small>
                  </li>
                </ul>
              </template>
            </div>

            <div v-show="!post.is_editing" class="col-12" :class="{'col-lg-10':is_mode_linear}">
              <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"/>
              <div class="post-vote">
                <strong
                  v-if="is_mode_linear"
                  class="vote-count"
                  title="Ocena posta"
                  @click="loadVoters(post)"
                  :aria-label="voters"
                  data-balloon-pos="left"
                  data-balloon-break
                  v-text="post.score"
                />
                <span
                  v-if="!hidden && is_mode_linear"
                  :aria-label="voters"
                  @click="checkAuth(vote, post)"
                  @mouseenter.once="loadVoters(post)"
                  data-balloon-pos="left"
                  data-balloon-break
                  class="vote-up cursor-pointer">
                  <vue-icon name="postVoted" v-if="post.is_voted" class="neon-primary-color"/>
                  <vue-icon name="postVote" v-else/>
                </span>
                <span v-if="post.is_accepted" class="vote-accept on">
                  <vue-icon name="postAccept"/>
                </span>
              </div>
              <div class="post-content neon-contains-a-color-link" :style="is_mode_tree ? {minHeight:'initial'} : {}">
                <div v-html="post.html" ref="postContent"/>
                <ul v-if="post.assets.length" class="list-unstyled mb-1">
                  <li v-for="asset in post.assets" class="small">
                    <vue-icon name="postAssetDownload"/>
                    {{' '}}
                    <a :href="`/assets/${asset.id}/${asset.name}`">{{asset.name}}</a>
                    {{' '}}
                    <small>({{size(asset.size)}}) - <em>ściągnięć: {{asset.count}}</em></small>
                  </li>
                </ul>
                <template v-if="signatureVisible">
                  <hr>
                  <footer v-html="post.user.sig"/>
                </template>
              </div>
              <vue-tags :tags="tags" class="tag-clouds-md mt-2 mb-2"/>
              <div v-if="post.edit_count && is_mode_linear" class="edit-info">
                <strong>
                  edytowany {{post.edit_count}}x, ostatnio:
                  <vue-username :user="post.editor"/>
                </strong>
                {{' '}}
                <vue-timeago :datetime="post.updated_at"/>
              </div>
              <div class="post-comments" v-if="is_mode_linear">
                <div v-if="post.comments_count > Object.keys(post.comments).length"
                     class="d-inline-block mb-2 show-all-comments">
                  <span @click="loadComments(post)" class="cursor-pointer">
                    <vue-icon name="postFoldedCommentsUnfold"/>
                    Zobacz
                    {{declination(totalComments, ['pozostały', 'pozostałe', 'pozostałe'])}}
                    {{totalComments}}
                    {{declination(totalComments, ['komentarz', 'komentarze', 'komentarzy'])}}
                  </span>
                </div>
                <vue-comment
                  v-for="comment in post.comments"
                  :key="comment.id"
                  :navbar-offset="navigationBarOffset"
                  :comment="comment"/>
                <vue-comment-form
                  v-show="isCommenting"
                  :comment="commentDefault"
                  @save="isCommenting = false"
                  @cancel="isCommenting = false"
                  ref="comment-form"
                  class="mt-2"/>
              </div>
            </div>

            <vue-form
              v-if="post.is_editing"
              ref="form"
              class="col-12 mt-2 mb-2"
              :class="{'col-lg-10':is_mode_linear}"
              :post="post"
              :show-title-input="isFirstPost"
              :show-discuss-mode-select="false"
              :show-tags-input="isFirstPost"
              :show-sticky-checkbox="isFirstPost && post.moderatorPermissions.sticky"
              :upload-mimes="uploadMimes"
              :upload-max-size="uploadMaxSize"
              @cancel="$store.commit('posts/editEnd', post)"
              @save="$store.commit('posts/editEnd', post)"
            />
          </div>
        </div>
        <div :class="{'collapse': isCollapsed, 'backport-post-footer':is_mode_linear}" class="px-2 py-1" v-if="!authorBlocked">
          <div class="row">
            <div class="d-none" :class="{'d-lg-block col-lg-2':is_mode_linear}" v-if="is_mode_linear"/>
            <div class="col-12 d-flex" :class="{'col-lg-10':is_mode_linear}">
              <div v-if="!post.deleted_at">
                <span
                  v-if="!hidden && is_mode_tree"
                  class="post-action"
                  @click="checkAuth(vote, post)"
                  :aria-label="voters"
                  data-balloon-pos="up"
                  data-balloon-break
                >
                  <span v-if="post.is_voted" class="text-primary">
                    <vue-icon name="postVoted"/>
                  </span>
                  <vue-icon name="postVote" v-else/>
                  <span v-text="post.score" class="ms-1"/>
                  <span class="d-none d-sm-inline ms-1">Doceń</span>
                </span>

                <div class="dropdown d-inline-block">
                  <span class="post-action" data-bs-toggle="dropdown">
                    <vue-icon name="postShare"/>
                    <span class="d-none d-sm-inline ms-1">Udostępnij</span>
                  </span>
                  <div class="dropdown-menu dropdown-menu-start">
                    <span v-for="item in shareDropdownItems" class="dropdown-item" @click="item.action">
                      <vue-icon :name="item.iconName"/>
                      {{item.title}}
                    </span>
                  </div>
                </div>
                <span class="post-action" v-if="post.permissions.accept" @click="accept(post)"
                      title="Kliknij, aby ustawić tę odpowiedź jako zaakceptowaną">
                  <template v-if="post.is_accepted">
                    <vue-icon name="postAcceptAccepted" class="text-primary"/>
                    <span class="d-none d-sm-inline ms-1">Zaakceptuj</span>
                  </template>
                  <template v-else>
                    <vue-icon name="postAccept"/>
                    <span class="d-none d-sm-inline ms-1">Zaakceptuj</span>
                  </template>
                </span>
                <span v-if="is_mode_linear && (!post.is_locked || post.permissions.write)" @click="checkAuth(comment)" class="post-action">
                  <span v-if="isCommenting" class="text-primary">
                    <vue-icon name="postCommentActive"/>
                  </span>
                  <vue-icon v-else name="postComment"/>
                  <span class="d-none d-sm-inline ms-1">Komentuj</span>
                </span>
              </div>
              <div v-if="post.permissions.write" :class="{'ms-auto':is_mode_linear}">
                <template v-if="!post.deleted_at">
                  <span @click="replyMentionAuthor" class="post-action" title="Odpowiedz na ten post" v-if="is_mode_linear">
                    <vue-icon name="postMentionAuthor"/>
                  </span>
                  <span @click="replyQuoteContent" class="post-action" title="Dodaj cytat do pola odpowiedzi" v-if="!treeTopicPostFirst">
                    <vue-icon name="postAnswerQuote"/>
                    <span class="d-none d-sm-inline ms-1">Odpowiedz</span>
                  </span>
                </template>
                <div v-if="postDropdownVisible" class="dropdown float-end">
                  <span class="post-action" data-bs-toggle="dropdown">
                    <vue-icon name="postMenuDropdown"/>
                  </span>
                  <div class="dropdown-menu dropdown-menu-end">
                    <template v-for="item in postDropdownItems">
                      <div v-if="item.divider" class="dropdown-divider"/>
                      <a v-else-if="item.link" class="dropdown-item" :href="item.link">
                        <vue-icon :name="item.iconName"/>
                        {{item.title}}
                      </a>
                      <span
                        v-else
                        class="dropdown-item"
                        :class="{disabled: item.disabled}"
                        @click="item.action">
                        <vue-icon :name="item.iconName"/>
                        {{item.title}}
                      </span>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row" v-if="is_mode_tree && !post.deleted_at && treeTopicReplyVisible">
          <div class="d-none d-lg-block col-lg-2" v-if="is_mode_linear"/>
          <div class="col-12" :class="{'col-lg-10':is_mode_linear}">
            <div class="px-2 pb-2" :class="{'ps-lg-0 pe-lg-4 pb-lg-3':is_mode_linear}">
              <vue-form :tree-answer-post-id="post.id" :post="postDefault" @save="formSaved" ref="topicReply"/>
            </div>
          </div>
        </div>
        <vue-modal ref="delete-modal" @delete="deletePostCloseModalDelete" :reasons="reasons"/>
      </div>
      <vue-gallery :images="galleryImages" :index="galleryImageIndex" @close="closeGallery"/>
    </div>
  </div>
  <div style="margin-bottom:18px;">
    <div class="tree-post position-relative" :class="postIndentCssClasses" v-if="hasDeeperChildren || childrenFolded">
      <vue-post-guiderail
        v-if="guiderailVisible"
        link-to-child="none"
        :links-to-parent="false"
        :parent-levels="parentLevels"
        :expanded="childrenFolded"
        @toggle="guiderailToggle"
      />
      <div class="d-flex align-items-center" style="margin-left:50px;">
        <div v-if="hasDeeperChildren">
          <a class="me-2" :href="postSubTreeUrl">
            <vue-icon name="postGuiderailExpanded"/>
            {{postAnswersAuthorsSeeMore}}
          </a>
        </div>
        <div v-if="childrenFolded && hasChildren">
          <a class="me-2" @click="unfoldChildren" href="javascript:">
            <vue-icon name="postGuiderailExpanded"/>
            {{postAnswersAuthorsSeeMore}}
          </a>
        </div>
        <div v-for="author in postAnswersAuthorsDistinct" style="width:38px;">
          <vue-avatar
            :photo="author.photo"
            :name="author.name"
            :initials="author.initials"
            class="img-thumbnail me-1"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import pl from 'date-fns/locale/pl';
import {mapActions, mapGetters, mapState} from "vuex";
import Icon from "../../../../neon2/web/src/Infrastructure/Vue/Icon/Icon.vue";

import declination from '../../libs/declination';
import {copyToClipboard} from '../../plugins/clipboard';
import {openFlagModal} from "../../plugins/flags";
import {confirmModal} from "../../plugins/modals";
import {formatTimeAgo, VueTimeAgo} from "../../plugins/timeago.js";
import store from "../../store/index";
import {notify} from "../../toast";
import {User} from "../../types/models";
import {nextTick} from "../../vue";
import VueAvatar from '../avatar.vue';
import VueDeleteModal from "../delete-modal.vue";
import VueIcon from "../icon";
import {default as mixins} from '../mixins/user.js';
import VueTags from '../tags.vue';
import VueUserName from "../user-name.vue";
import VueGallery from "./../../components/microblog/gallery.vue";
import VueFlag from './../flags/flag.vue';
import VueButton from './../forms/button.vue';
import VueSelect from './../forms/select.vue';
import VueCommentForm from "./comment-form.vue";
import VueComment from './comment.vue';
import VueForm from './form.vue';
import VuePostGuiderail, {ChildLink} from "./post-guiderail.vue";
import UserStatusBanned from "./UserStatusBanned.vue";

export default {
  name: 'post',
  mixins: [mixins],
  components: {
    Icon,
    'vue-gallery': VueGallery,
    VuePostGuiderail,
    'vue-avatar': VueAvatar,
    'vue-button': VueButton,
    'vue-comment': VueComment,
    'vue-comment-form': VueCommentForm,
    'vue-flag': VueFlag,
    'vue-form': VueForm,
    'vue-icon': VueIcon,
    'vue-modal': VueDeleteModal,
    'vue-select': VueSelect,
    'vue-tags': VueTags,
    'vue-timeago': VueTimeAgo,
    'vue-username': VueUserName,
    'vue-user-status-banned': UserStatusBanned,
  },
  emits: ['reply'],
  props: {
    post: {type: Object, required: true},
    treeItem: {type: Object, required: false},
    uploadMaxSize: {type: Number, default: 20},
    uploadMimes: {type: String},
    treeTopicPostFirst: {type: Boolean, required: false},
  },
  data() {
    return {
      isProcessing: false,
      isCollapsed: false,
      isCommenting: false,
      commentDefault: {text: '', post_id: this.post.id},
      postDefault: {text: '', html: '', assets: []},
      treeTopicReplyVisible: false,
      galleryImages: [],
      galleryImageIndex: null as number|null,
      navigationBarOffset: 92,
    };
  },
  created(): void {
    this.$data.isCollapsed = this.hidden;
  },
  mounted(): void {
    if (this.is_mode_tree && !this.post.deleted_at) {
      this.loadVoters(this.post);
    }
    this.resetGalleryImages();
  },
  watch: {
    post(): void {
      this.$data.galleryImages = [];
      nextTick(() => {
        this.resetGalleryImages();
      });
    },
  },
  methods: {
    resetGalleryImages(): void {
      const postContent = this.$refs['postContent'];
      const images = postContent.querySelectorAll('img:not(.img-smile)');
      images.forEach((image, index) => {
        this.$data.galleryImages.push(image.src);
        image.addEventListener('click', () => {
          this.$data.galleryImageIndex = index;
        });
      });
    },
    closeGallery(): void {
      this.$data.galleryImageIndex = null;
    },
    toggleDeletedPost(): void {
      if (!this.postObscured) {
        this.$data.isCollapsed = !this.$data.isCollapsed;
      }
    },
    unfoldChildren(): void {
      store.commit('posts/unfoldChildren', this.$props.post);
    },
    guiderailToggle(expanded: boolean): void {
      if (expanded) {
        store.commit('posts/foldChildren', this.$props.post);
      } else {
        store.commit('posts/unfoldChildren', this.$props.post);
      }
    },
    ...mapActions('posts', ['vote', 'accept', 'subscribe', 'unsubscribe', 'loadComments', 'loadVoters']),
    formatDistanceToNow(date) {
      return formatDistanceToNow(new Date(date), {locale: pl});
    },
    copy(text: string, successMessage: string): void {
      if (copyToClipboard(text)) {
        notify({type: 'success', text: successMessage});
      } else {
        notify({type: 'error', text: 'Nie można skopiować linku. Sprawdź ustawienia przeglądarki.'});
      }
    },
    edit() {
      store.commit('posts/editStart', this.post);
      nextTick(() => (this.$refs.form as VueForm).$refs.markdown.focus());
    },
    comment() {
      this.$data.isCommenting = !this.$data.isCommenting;
      if (this.$data.isCommenting) {
        nextTick(() => (this.$refs['comment-form'] as typeof VueCommentForm).focus());
      }
    },
    deletePostOpenModal(): void {
      (this.$refs['delete-modal'] as typeof VueDeleteModal).open();
    },
    deletePostCloseModalDelete(reasonId: number): void {
      (this.$refs['delete-modal'] as typeof VueDeleteModal)!.close();
      store.dispatch('posts/delete', {post: this.post, reasonId})
        .then(() => this.$data.isCollapsed = true);
    },
    merge() {
      confirmModal({
        message: 'Czy chcesz połaczyć ten post z poprzednim?',
        title: 'Połączyć posty?',
        okLabel: 'Tak, połącz',
      }).then(() => {
        store.dispatch('posts/merge', this.post);
      });
    },
    restore() {
      this.$data.isCollapsed = false;
      store.dispatch('posts/restore', this.post);
    },
    flagPost(): void {
      const post = this.$props.post;
      openFlagModal(post.url, post.metadata);
    },
    replyMentionAuthor(): void {
      this.$emit('reply', this.$props.post);
    },
    replyQuoteContent(): void {
      if (store.getters['topics/is_mode_tree']) {
        this.$data.treeTopicReplyVisible = !this.$data.treeTopicReplyVisible;
        if (this.$data.treeTopicReplyVisible) {
          nextTick(() => {
            this.$refs.topicReply.focus();
          });
        }
      } else {
        this.$emit('reply', this.$props.post, false);
      }
    },
    formSaved(): void {
      this.$data.treeTopicReplyVisible = false;
    },
    copyPostLink(): void {
      this.copy(this.$props.post.url, 'Link do postu znajduje się w schowku.');
    },
    copyPostLinkMarkdown(): void {
      this.copy(markdownLink(this.$props.post.id, this.$props.post.url), 'Link Markdown do postu znajduje się w schowku.');
    },
  },
  computed: {
    ...mapState('user', ['user']),
    ...mapState('topics', ['reasons']),
    ...mapGetters('user', ['isAuthorized']),
    ...mapGetters('posts', ['posts', 'isLinearized']),
    ...mapGetters('topics', ['topic', 'is_mode_tree', 'is_mode_linear']),
    formatBrowsingDevice(): string {
      const device = this.$props.post.browsingDevice;
      return device.browser + '/' + device.platform + '/' + device.type;
    },
    postTreeTargetId(): number {
      return store.getters['posts/treeTopicPostTargetId'](this.$props.post.id);
    },
    hasPostTreeTarget(): boolean {
      return store.getters['posts/treeTopicPostTargetId'](this.$props.post.id) !== null;
    },
    childrenFolded(): boolean {
      return this.$props.post.childrenFolded;
    },
    postAnswersAuthors(): User[] {
      if (this.$props.treeItem) {
        return this.$props.treeItem.childrenAuthors;
      }
      return [];
    },
    hasChildren(): boolean {
      return this.postAnswersAuthors.length > 0;
    },
    postAnswersAuthorsDistinct(): User[] {
      const map = new Map<number, User>();
      for (const author of this.postAnswersAuthors) {
        map.set(author.id, author);
      }
      return Array.from(map.values());
    },
    postAnswersAuthorsSeeMore(): string {
      const answers = this.postAnswersAuthors.length;
      return `Zobacz ${answers} ${declination(answers, ['odpowiedź', 'odpowiedzi', 'odpowiedzi'])}.`;
    },
    editedTimeAgo() {
      return formatTimeAgo(this.$props.post.updated_at);
    },
    postObscured(): boolean {
      return this.$props.post.type === 'obscured';
    },
    postIndentCssClasses(): string[] {
      if (!this.$props.treeItem) return [];
      const indent = this.$props.treeItem.indent;
      const indentCssClasses = [
        'indent-none', 'indent-1', 'indent-2', 'indent-3', 'indent-4', 'indent-5',
        'indent-6', 'indent-7', 'indent-8', 'indent-9', 'indent-10', 'indent-11',
      ];
      return ['indent', indentCssClasses[indent]];
    },
    guiderailVisible(): boolean {
      return !!this.$props.treeItem;
    },
    linksToParent(): boolean {
      return this.$props.treeItem.linksToParent;
    },
    hasDeeperChildren(): boolean {
      if (this.$props.treeItem) {
        return this.$props.treeItem.hasDeeperChildren;
      }
      return false;
    },
    postSubTreeUrl(): string {
      return this.$props.post.url;
    },
    linkToChild(): ChildLink {
      if (!this.$props.treeItem.linksToChildren) {
        return 'none';
      }
      if (this.childrenFolded) {
        return 'toggle-only';
      }
      return 'toggle-and-link';
    },
    parentLevels(): number[] {
      return this.$props.treeItem.parentLevels;
    },
    postDropdownVisible(): boolean {
      return this.postDropdownItems.length > 0;
    },
    postDropdownItems(): object[] {
      const items = [];
      const post: Post = this.$props.post;
      if (post.permissions.update) {
        items.push({title: 'Edytuj', iconName: 'postEdit', action: this.edit, disabled: post.deleted_at || post.is_editing});
      }
      if (post.permissions.delete) {
        items.push({title: 'Usuń', iconName: 'postDelete', action: this.deletePostOpenModal});
      }
      if (post.is_subscribed) {
        items.push({title: 'Przestań obserwować', iconName: 'postSubscribed', action: () => this.checkAuth(this.unsubscribe, post)});
      } else {
        items.push({title: 'Obserwuj', iconName: 'postSubscribe', action: () => this.checkAuth(this.subscribe, post)});
      }
      if (!post.deleted_at) {
        items.push({title: 'Zgłoś', iconName: 'postReport', action: this.flagPost});
      }
      const canMerge = this.is_mode_linear;
      const mod = post.moderatorPermissions;
      if (mod.delete || mod.update || mod.accept || (mod.merge && canMerge)) {
        items.push({divider: true});
      }
      if (mod.accept) {
        if (post.is_accepted) {
          items.push({title: 'Usuń akceptację jako moderator', iconName: 'postAcceptAccepted', action: () => this.accept(post)});
        } else {
          items.push({title: 'Zaakceptuj jako moderator', iconName: 'postAccept', action: () => this.accept(post)});
        }
      }
      if (mod.update) {
        items.push({title: 'Edytuj jako moderator', iconName: 'postEdit', action: this.edit, disabled: post.deleted_at || post.is_editing});
        items.push({title: 'Historia edycji', iconName: 'postEditHistoryShow', link: '/Forum/Post/Log/' + post.id});
      }
      if (mod.delete) {
        if (post.deleted_at) {
          items.push({title: 'Przywróć', iconName: 'postRestore', action: this.restore});
        } else {
          items.push({title: 'Usuń jako moderator', iconName: 'postDelete', action: this.deletePostOpenModal});
        }
      }
      if (mod.merge && canMerge) {
        items.push({
          title: 'Połącz z poprzednim',
          iconName: 'postMergeWithPrevious',
          action: this.merge,
          disabled: post.deleted_at || this.isFirstPost,
        });
      }
      return items;
    },
    shareDropdownItems(): object[] {
      const items = [];
      items.push({title: 'Kopiuj link do postu ', iconName: 'postCopyLinkPost', action: this.copyPostLink});
      items.push({title: 'Kopiuj link do postu jako Markdown', iconName: 'postCopyLinkPost', action: this.copyPostLinkMarkdown});
      return items;
    },
    voters() {
      const users = this.post.voters;
      if (!users?.length) {
        return null;
      }
      return users.length > 10 ? users.slice(0, 10).concat('...').join("\n") : users.join("\n");
    },
    otherVoters(): string[]|null {
      if (this.post.voters) {
        return this.post.voters.filter((name: string) => name !== this.user.name);
      }
      return null;
    },
    tags() {
      return this.isFirstPost ? this.topic.tags : [];
    },
    anchor() {
      return `id${this.post.id}`;
    },
    highlight(): boolean {
      return this.post.highlighted;
    },
    totalComments() {
      return this.post.comments_count - Object.keys(this.post.comments).length;
    },
    flags() {
      return store.getters['flags/filter'](this.post.id, 'Coyote\\Post');
    },
    hidden(): boolean {
      return this.post.deleted_at || this.authorBlocked;
    },
    authorBlocked(): boolean {
      return this.post.user_id && store.getters['user/isBlocked'](this.post.user_id);
    },
    isFirstPost(): boolean {
      return this.$props.post.id === this.topic.first_post_id;
    },
    signatureVisible(): boolean {
      if (this.is_mode_tree && !this.isFirstPost) {
        return false;
      }
      return this.$props.post.user && this.$props.post.user.sig;
    },
  },
};

function markdownLink(postId: number, postUrl: string): string {
  return `[#${postId}](${postUrl})`;
}
</script>

<template>
  <div :class="{'sequential': message.sequential, 'unread': ! isRead}" class="media">
    <a v-if="!message.sequential" v-profile2="message.user" class="me-2 d-none d-sm-block flex-shrink-0">
      <div class="neon-avatar-border">
        <vue-avatar
          :photo="message.user.photo"
          :name="message.user.name"
          :initials="message.user.initials"
          class="i-45"/>
      </div>
    </a>
    <div class="media-body">
      <template v-if="!message.sequential">
        <small class="float-end text-muted">
          <vue-timeago :datetime="message.created_at"/>
        </small>
        <h3>
          <vue-username v-if="clickableText" :user="message.user" :href="'/User/Pm/Show/' + message.id"></vue-username>
          <vue-username v-else :user="message.user"></vue-username>
        </h3>
      </template>
      <a @click="deleteMessage" class="btn-delete float-end text-danger" href="javascript:" title="Usuń">
        <vue-icon name="privateMessageDelete"/>
      </a>
      <a v-if="clickableText" :href="'/User/Pm/Show/' + message.id" class="excerpt">
        {{message.excerpt ? message.excerpt : '(kliknij, aby przeczytać)'}}
      </a>
      <div v-else class="pm-text neon-contains-a-color-link" v-html="message.text"/>
      <small v-if="last && message.folder === SENTBOX && message.read_at" class="text-muted">
        <vue-icon name="privateMessageReadAt"/>
        Przeczytano,
        <vue-timeago :datetime="message.read_at"/>
      </small>
    </div>
  </div>
</template>

<script lang="ts">
import {confirmModal} from "../../plugins/modals";
import {VueTimeAgo} from '../../plugins/timeago.js';
import store from "../../store/index";
import {MessageFolder} from '../../types/models';
import VueAvatar from '../avatar.vue';
import VueIcon from "../icon";
import {default as mixins} from '../mixins/user.js';
import VueUserName from '../user-name.vue';

export default {
  name: 'VueMessage',
  mixins: [mixins],
  components: {
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-timeago': VueTimeAgo,
    'vue-icon': VueIcon,
  },
  props: {
    message: {
      type: Object,
      required: true,
    },
    last: {
      type: Boolean,
      default: false,
    },
    clickableText: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      SENTBOX: MessageFolder.sentbox,
    };
  },
  methods: {
    deleteMessage() {
      confirmModal({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć tę wiadomość?',
        okLabel: 'Tak, usuń',
      }).then(() =>
        store.dispatch(`messages/${this.clickableText ? 'trash' : 'remove'}`, this.message),
      );
    },
  },
  computed: {
    isRead() {
      return this.message.folder !== MessageFolder.sentbox ? this.message.read_at !== null : true;
    },
    excerpt() {
      return this.clickableText ? (this.message.excerpt ? this.message.excerpt : '(kliknij, aby przeczytać)') : this.message.text;
    },
  },
};
</script>

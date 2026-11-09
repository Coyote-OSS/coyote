<template>
  <div class="pm-chat neon-tile neon-rounded-top" :class="{'pm-chat--minimized': minimized}">
    <header class="pm-chat__header" @click="$emit('toggle')">
      <vue-avatar class="pm-chat__avatar" :photo="recipient.photo" :name="recipient.name" :initials="recipient.initials"/>
      <span class="pm-chat__name">{{ recipient.name }}</span>
      <span v-if="minimized && unreadCount" class="pm-chat__unread">{{ unreadCount }}</span>
      <a :href="fullConversationUrl" class="pm-chat__button" title="Otwórz pełną rozmowę" @click.stop>
        <vue-icon name="homepageChatFullConversation"/>
      </a>
      <button type="button" class="pm-chat__button" :title="minimized ? 'Rozwiń' : 'Zwiń'" @click.stop="$emit('toggle')">
        <vue-icon name="homepageChatMinimize"/>
      </button>
      <button type="button" class="pm-chat__button" title="Zamknij" @click.stop="$emit('close')">
        <vue-icon name="homepageChatClose"/>
      </button>
    </header>

    <template v-if="!minimized">
      <div class="pm-chat__messages" ref="messages" @scroll="onScroll">
        <div v-if="isLoading" class="pm-chat__status">
          <vue-icon name="homepageChatLoading" spin/>
        </div>
        <div v-else-if="loadError" class="pm-chat__status pm-chat__status--error">
          Nie udało się wczytać wiadomości.
          <button type="button" class="pm-chat__retry" @click="loadHistory">Spróbuj ponownie</button>
        </div>
        <div v-else-if="!messages.length" class="pm-chat__status">
          Napisz pierwszą wiadomość do {{ recipient.name }}.
        </div>
        <div v-if="loadMoreError" class="pm-chat__status pm-chat__status--error pm-chat__status--more">
          Nie udało się wczytać starszych wiadomości.
          <button type="button" class="pm-chat__retry" @click="loadMore">Spróbuj ponownie</button>
        </div>
        <div
          v-for="(message, index) in messages"
          :key="message.id"
          class="pm-chat__message"
          :class="isOwn(message) ? 'pm-chat__message--own' : 'pm-chat__message--theirs'"
          :title="formatDate(message.created_at)">
          <div class="pm-chat__bubble neon-contains-a-color-link" v-html="message.text"/>
          <small v-if="index === lastOwnIndex && message.read_at" class="pm-chat__read">Przeczytano</small>
        </div>
      </div>

      <div class="pm-chat__typing">
        <small v-if="isTyping"><em>{{ recipient.name }} pisze...</em></small>
      </div>

      <form class="pm-chat__form" @submit.prevent="send">
        <textarea
          ref="input"
          v-model="text"
          class="pm-chat__input"
          rows="1"
          placeholder="Napisz wiadomość..."
          @keydown.enter.exact.prevent="send"
          @input="typing"/>
        <button type="submit" class="pm-chat__send" title="Wyślij" :disabled="isSending || !text.trim()">
          <vue-icon :name="isSending ? 'homepageChatLoading' : 'homepageChatSend'" :spin="isSending"/>
        </button>
      </form>
      <div v-if="error" class="pm-chat__error">{{ error }}</div>
    </template>
  </div>
</template>

<script lang="ts">
import axios from 'axios';
import {PropType} from 'vue';

import {default as ws} from '../../libs/realtime';
import {loadDraft, removeDraft, saveDraft} from '../../plugins/autosave';
import store from '../../store/index';
import {MessageFolder} from '../../types/models';
import VueAvatar from '../avatar.vue';
import VueIcon from '../icon';

export interface ChatRecipient {
  id: number;
  name: string;
  photo: string | null;
  initials: string;
}

const PAGE_SIZE = 10;

export function privateChannel(recipientId: number): string {
  return 'private:' + [store.state.user.user.id, recipientId].sort((a, b) => a - b).join('');
}

function draftKey(recipientId: number): string {
  return `pm-chat:draft:${recipientId}`;
}

export default {
  name: 'VuePmChat',
  components: {
    'vue-avatar': VueAvatar,
    'vue-icon': VueIcon,
  },
  props: {
    recipient: {type: Object as PropType<ChatRecipient>, required: true},
    minimized: {type: Boolean, default: false},
  },
  emits: ['toggle', 'close'],
  data() {
    return {
      messages: [] as any[],
      hasMore: false,
      isLoading: false,
      isLoadingMore: false,
      loadError: false,
      loadMoreError: false,
      isSending: false,
      isTyping: false,
      typingTimer: null as number | null,
      text: loadDraft(draftKey(this.recipient.id)),
      error: null as string | null,
    };
  },
  mounted() {
    this.loadHistory();
  },
  unmounted() {
    clearTimeout(this.typingTimer);
  },
  watch: {
    text(value: string) {
      value ? saveDraft(draftKey(this.recipient.id), value) : removeDraft(draftKey(this.recipient.id));
    },
  },
  methods: {
    loadHistory() {
      this.isLoading = true;
      this.loadError = false;
      this.loadMoreError = false;
      this.fetch(0)
        .then(messages => {
          // messages received live while the history was loading are kept
          const loaded = new Set(messages.map(message => message.id));
          this.messages = [...messages, ...this.messages.filter(message => !loaded.has(message.id))];
          this.afterExpand();
        })
        .catch(() => this.loadError = true)
        .finally(() => this.isLoading = false);
    },

    afterExpand() {
      if (this.minimized) {
        return;
      }
      this.$nextTick(() => {
        this.scrollToBottom();
        this.$refs.input?.focus();
      });
      this.markAsRead();
    },

    fetch(offset: number): Promise<any[]> {
      return axios.get('/User/Pm/Infinity', {params: {author_id: this.recipient.id, offset}})
        .then(response => {
          const messages = response.data.data;
          this.hasMore = messages.length === PAGE_SIZE;
          return messages;
        });
    },

    onScroll() {
      const container = this.$refs.messages as HTMLElement;
      if (container.scrollTop > 20 || this.loadMoreError) {
        return;
      }
      this.loadMore();
    },

    loadMore() {
      if (!this.hasMore || this.isLoadingMore) {
        return;
      }
      const container = this.$refs.messages as HTMLElement;
      this.isLoadingMore = true;
      this.loadMoreError = false;
      const previousHeight = container.scrollHeight;
      this.fetch(this.messages.length)
        .then(messages => {
          this.messages = [...messages, ...this.messages];
          // keep the message the user was looking at in place
          this.$nextTick(() => container.scrollTop = container.scrollHeight - previousHeight);
        })
        .catch(() => this.loadMoreError = true)
        .finally(() => this.isLoadingMore = false);
    },

    send() {
      if (this.isSending || !this.text.trim()) {
        return;
      }
      this.isSending = true;
      const recipientId = this.recipient.id;
      axios.post('/User/Pm/Submit', {recipient: this.recipient.name, text: this.text, assets: []})
        .then(response => {
          this.error = null;
          this.messages.push(response.data);
          this.text = '';
          this.$nextTick(() => this.scrollToBottom());
          removeDraft(draftKey(recipientId));
        })
        .catch(error => {
          const errors = error.response?.data?.errors;
          this.error = errors ? (Object.values(errors)[0] as string[])[0] : 'Nie udało się wysłać wiadomości.';
        })
        .finally(() => {
          this.isSending = false;
          this.$nextTick(() => this.$refs.input?.focus());
        });
    },

    markAsRead() {
      const unread = this.unreadMessages;
      if (!unread.length || this.minimized) {
        return;
      }
      const now = new Date().toISOString();
      unread.forEach(message => message.read_at = now);
      axios.post(`/User/Pm/Mark/${unread[unread.length - 1].id}`)
        .then(response => store.commit('inbox/SET_COUNT', response.data.count));
    },

    // called by the dock, which listens for websocket events of all the open chats
    receiveMessage(message) {
      if (this.messages.some(item => item.id === message.id)) {
        return;
      }
      this.messages.push(message);
      this.isTyping = false;
      if (!this.minimized) {
        this.$nextTick(() => this.scrollToBottom());
        if (document.visibilityState === 'visible') {
          this.markAsRead();
        }
      }
    },

    // a message was read by the recipient
    messageRead(textId: number) {
      const message = this.messages.find(item => item.text_id === textId);
      if (message) {
        message.read_at = new Date().toISOString();
      }
    },

    recipientTyping() {
      this.isTyping = true;
      clearTimeout(this.typingTimer);
      this.typingTimer = setTimeout(() => this.isTyping = false, 1000);
    },

    typing() {
      const user = store.state.user.user;
      ws.subscribe(this.privateChannel).whisper('pm-typing', {recipient: {id: user.id, name: user.name}});
    },

    scrollToBottom() {
      const container = this.$refs.messages as HTMLElement | undefined;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    },

    isOwn(message): boolean {
      return message.folder === MessageFolder.sentbox;
    },

    formatDate(date: string): string {
      return new Date(date).toLocaleString('pl-PL');
    },
  },
  computed: {
    fullConversationUrl(): string {
      return '/User/Pm/Submit?to=' + encodeURIComponent(this.recipient.name);
    },

    unreadMessages(): any[] {
      return this.messages.filter(message => !this.isOwn(message) && message.read_at === null);
    },

    unreadCount(): number {
      return this.unreadMessages.length;
    },

    lastOwnIndex(): number {
      return this.messages.map(message => this.isOwn(message)).lastIndexOf(true);
    },

    privateChannel(): string {
      return privateChannel(this.recipient.id);
    },
  },
};
</script>

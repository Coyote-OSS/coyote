<template>
  <div v-if="recipient" class="pm-chat neon-tile neon-rounded-top" :class="{'pm-chat--minimized': minimized}">
    <header class="pm-chat__header" @click="toggleMinimized">
      <vue-avatar class="pm-chat__avatar" :photo="recipient.photo" :name="recipient.name" :initials="recipient.initials"/>
      <span class="pm-chat__name">{{ recipient.name }}</span>
      <span v-if="minimized && unreadCount" class="pm-chat__unread">{{ unreadCount }}</span>
      <a :href="fullConversationUrl" class="pm-chat__button" title="Otwórz pełną rozmowę" @click.stop>
        <vue-icon name="homepageChatFullConversation"/>
      </a>
      <button type="button" class="pm-chat__button" :title="minimized ? 'Rozwiń' : 'Zwiń'" @click.stop="toggleMinimized">
        <vue-icon name="homepageChatMinimize"/>
      </button>
      <button type="button" class="pm-chat__button" title="Zamknij" @click.stop="close">
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

const STATE_KEY = 'pm-chat';
const PAGE_SIZE = 10;

function draftKey(recipientId: number): string {
  return `pm-chat:draft:${recipientId}`;
}

function loadState(): { recipient: ChatRecipient, minimized: boolean } | null {
  try {
    const state = JSON.parse(localStorage.getItem(STATE_KEY) as string);
    return state?.recipient ? state : null;
  } catch {
    return null;
  }
}

export default {
  name: 'VuePmChat',
  components: {
    'vue-avatar': VueAvatar,
    'vue-icon': VueIcon,
  },
  data() {
    return {
      recipient: null as ChatRecipient | null,
      minimized: false,
      messages: [] as any[],
      hasMore: false,
      isLoading: false,
      isLoadingMore: false,
      loadError: false,
      loadMoreError: false,
      isSending: false,
      isTyping: false,
      typingTimer: null as number | null,
      text: '',
      error: null as string | null,
      typingChannels: new Set<string>(),
    };
  },
  mounted() {
    this.listenForMessages();
    const state = loadState();
    if (state) {
      this.show(state.recipient, state.minimized);
    }
  },
  watch: {
    text(value: string) {
      if (this.recipient) {
        value ? saveDraft(draftKey(this.recipient.id), value) : removeDraft(draftKey(this.recipient.id));
      }
    },
  },
  methods: {
    open(recipient: ChatRecipient) {
      if (this.recipient?.id === recipient.id) {
        this.minimized = false;
        this.saveState();
        this.afterExpand();
        return;
      }
      this.show(recipient, false);
    },

    show(recipient: ChatRecipient, minimized: boolean) {
      this.recipient = recipient;
      this.minimized = minimized;
      this.messages = [];
      this.hasMore = false;
      this.error = null;
      this.isTyping = false;
      this.text = loadDraft(draftKey(recipient.id));
      this.saveState();
      this.listenForTyping();
      this.loadHistory();
    },

    loadHistory() {
      const recipientId = this.recipient.id;
      this.isLoading = true;
      this.loadError = false;
      this.loadMoreError = false;
      this.fetch(0)
        .then(messages => {
          if (this.recipient?.id !== recipientId) {
            return;
          }
          // messages received live while the history was loading are kept
          const loaded = new Set(messages.map(message => message.id));
          this.messages = [...messages, ...this.messages.filter(message => !loaded.has(message.id))];
          this.afterExpand();
        })
        .catch(() => {
          if (this.recipient?.id === recipientId) {
            this.loadError = true;
          }
        })
        .finally(() => {
          if (this.recipient?.id === recipientId) {
            this.isLoading = false;
          }
        });
    },

    close() {
      this.recipient = null;
      this.messages = [];
      localStorage.removeItem(STATE_KEY);
    },

    toggleMinimized() {
      this.minimized = !this.minimized;
      this.saveState();
      if (!this.minimized) {
        this.afterExpand();
      }
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

    saveState() {
      localStorage.setItem(STATE_KEY, JSON.stringify({recipient: this.recipient, minimized: this.minimized}));
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
      const recipientId = this.recipient.id;
      this.fetch(this.messages.length)
        .then(messages => {
          if (this.recipient?.id !== recipientId) {
            return;
          }
          this.messages = [...messages, ...this.messages];
          // keep the message the user was looking at in place
          this.$nextTick(() => container.scrollTop = container.scrollHeight - previousHeight);
        })
        .catch(() => {
          if (this.recipient?.id === recipientId) {
            this.loadMoreError = true;
          }
        })
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
          if (this.recipient?.id === recipientId) {
            this.messages.push(response.data);
            this.text = '';
            this.$nextTick(() => this.scrollToBottom());
          }
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

    listenForMessages() {
      const channel = ws.subscribe(`user:${store.state.user.user.id}`);

      channel.on('PmCreated', ({data}) => {
        if (data.user.id !== this.recipient?.id || this.messages.some(message => message.id === data.id)) {
          return;
        }
        this.messages.push(data);
        this.isTyping = false;
        if (!this.minimized) {
          this.$nextTick(() => this.scrollToBottom());
          if (document.visibilityState === 'visible') {
            this.markAsRead();
          }
        }
      });

      // a message was read by the recipient
      channel.on('PmRead', data => {
        const message = this.messages.find(item => item.text_id === data.text_id);
        if (message) {
          message.read_at = new Date().toISOString();
        }
      });

      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
          this.markAsRead();
        }
      });
    },

    listenForTyping() {
      const channelName = this.privateChannel;
      if (this.typingChannels.has(channelName)) {
        return;
      }
      this.typingChannels.add(channelName);
      ws.subscribe(channelName).on('pm-typing', data => {
        if (data.recipient.id !== this.recipient?.id) {
          return;
        }
        this.isTyping = true;
        clearTimeout(this.typingTimer);
        this.typingTimer = setTimeout(() => this.isTyping = false, 1000);
      });
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
      return 'private:' + [store.state.user.user.id, this.recipient.id].sort((a, b) => a - b).join('');
    },
  },
};
</script>

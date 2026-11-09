<template>
  <div class="pm-chat-dock">
    <vue-pm-chat
      v-for="chat in visibleChats"
      :key="chat.recipient.id"
      :ref="element => setChatRef(chat.recipient.id, element)"
      :recipient="chat.recipient"
      :minimized="chat.minimized"
      @toggle="toggle(chat)"
      @close="close(chat.recipient.id)"/>
  </div>
</template>

<script lang="ts">
import {default as ws} from '../../libs/realtime';
import store from '../../store/index';
import VuePmChat, {ChatRecipient, privateChannel} from './chat.vue';

interface OpenChat {
  recipient: ChatRecipient;
  minimized: boolean;
}

const STATE_KEY = 'pm-chats';
// see $pm-chat-width, $pm-chat-gap
const CHAT_WIDTH = 320;
const CHAT_GAP = 12;
const VIEWPORT_MARGIN = 16;

function loadState(): OpenChat[] {
  try {
    const state = JSON.parse(localStorage.getItem(STATE_KEY) as string);
    return Array.isArray(state) ? state.filter(chat => chat?.recipient) : [];
  } catch {
    return [];
  }
}

/**
 * Chat boxes with multiple people, side by side at the bottom of the page. The newest chat
 * is on the right; the oldest ones are closed when there's no more room for them.
 */
export default {
  name: 'VuePmChatDock',
  components: {'vue-pm-chat': VuePmChat},
  data() {
    return {
      chats: loadState() as OpenChat[],
      capacity: 1,
    };
  },
  created() {
    this.chatRefs = {};
    this.typingChannels = new Set<string>();
  },
  mounted() {
    this.updateCapacity();
    window.addEventListener('resize', this.updateCapacity);
    // the docked community panel moves the chats to the left
    const members = document.getElementById('home-members');
    if (members) {
      new MutationObserver(this.updateCapacity).observe(members, {attributes: true, attributeFilter: ['class']});
    }
    this.chats.forEach(chat => this.listenForTyping(chat.recipient.id));
    this.listenForMessages();
  },
  methods: {
    open(recipient: ChatRecipient) {
      const chat = this.chats.find(chat => chat.recipient.id === recipient.id);
      if (chat) {
        chat.minimized = false;
        this.saveState();
        this.$nextTick(() => this.chatRefs[recipient.id]?.afterExpand());
        return;
      }
      this.chats.push({recipient, minimized: false});
      // chats hidden for lack of room aren't kept, or they would pile up
      this.chats = this.chats.slice(-this.capacity);
      this.saveState();
      this.listenForTyping(recipient.id);
    },

    close(recipientId: number) {
      this.chats = this.chats.filter(chat => chat.recipient.id !== recipientId);
      this.saveState();
    },

    toggle(chat: OpenChat) {
      chat.minimized = !chat.minimized;
      this.saveState();
      if (!chat.minimized) {
        this.$nextTick(() => this.chatRefs[chat.recipient.id]?.afterExpand());
      }
    },

    saveState() {
      localStorage.setItem(STATE_KEY, JSON.stringify(this.chats));
    },

    setChatRef(recipientId: number, element) {
      if (element) {
        this.chatRefs[recipientId] = element;
      } else {
        delete this.chatRefs[recipientId];
      }
    },

    updateCapacity() {
      const right = parseFloat(getComputedStyle(this.$el).right) || 0;
      const available = window.innerWidth - right - VIEWPORT_MARGIN + CHAT_GAP;
      this.capacity = Math.max(1, Math.floor(available / (CHAT_WIDTH + CHAT_GAP)));
    },

    // websocket channels have no way to remove a listener, so each one is added only once, here
    listenForMessages() {
      const channel = ws.subscribe(`user:${store.state.user.user.id}`);
      channel.on('PmCreated', ({data}) => this.chatRefs[data.user.id]?.receiveMessage(data));
      channel.on('PmRead', data => Object.values(this.chatRefs).forEach((chat: any) => chat.messageRead(data.text_id)));

      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
          Object.values(this.chatRefs).forEach((chat: any) => chat.markAsRead());
        }
      });
    },

    listenForTyping(recipientId: number) {
      const channelName = privateChannel(recipientId);
      if (this.typingChannels.has(channelName)) {
        return;
      }
      this.typingChannels.add(channelName);
      ws.subscribe(channelName).on('pm-typing', data => this.chatRefs[data.recipient.id]?.recipientTyping());
    },
  },
  computed: {
    visibleChats(): OpenChat[] {
      return this.chats.slice(-this.capacity);
    },
  },
};
</script>

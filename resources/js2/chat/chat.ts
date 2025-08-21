import WebsocketTransport, {WebsocketMessage} from "../../../neon2/web/src/Infrastructure/Backend/WebsocketTransport";
import {createVueApp} from "../../js/vue";

const websocketSubscribeCommand = window['websocketSubscribeCommand'];

interface ChatMessage {
  message: string;
  authorUserId: number;
  authorUsername: string;
}

window.addEventListener('load', () => {
  let app;
  createVueApp('Chat', '#js-chat', {
    delimiters: ['${', '}'],
    data: () => {
      return {
        messageText: '',
        messages: [],
      };
    },
    created(): void {
      app = this;
    },
    methods: {
      send(): void {
        sendMessage(this.$data.messageText);
        this.$data.messageText = '';
      },
      addMessage(message: ChatMessage): void {
        this.$data.messages.push(`${message.authorUsername}: ${message.message}`);
      },
    },
  });
  const url = htmlMetaAttribute('websocket-url');
  const coyoteWebsocket = new WebsocketTransport(url, {
    connected(): void {
      coyoteWebsocket.send(websocketSubscribeCommand);
    },
    messageReceived(message: WebsocketMessage): void {
      if (message.event === 'chat-message') {
        app.addMessage(message.data as ChatMessage);
      }
    },
    closed(): void {},
  });
});

function sendMessage(message: string): void {
  httpRequest('POST', '/Chat/Message', {message},
    htmlMetaAttribute('csrf-token'));
}

function httpRequest(method: string, url: string, body: object, csrfToken: string): Promise<Response> {
  return fetch(url, {
    method,
    body: JSON.stringify(body),
    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
  });
}

function htmlMetaAttribute(metaAttribute: string): string {
  return document
    .querySelector('meta[name="' + metaAttribute + '"]')!
    .getAttribute('content')!;
}

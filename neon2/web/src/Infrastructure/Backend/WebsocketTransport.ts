export default class WebsocketTransport {
  private websocket: WebSocket|null = null;

  constructor(
    websocketUrl: string,
    private listener: WebsocketListener,
  ) {
    this.websocket = new WebSocket(websocketUrl);
    this.websocket.onopen = () => listener.connected();
    this.websocket.onmessage = event => listener.messageReceived(JSON.parse(event.data));
    this.websocket.onclose = () => listener.closed();
  }

  send(data: string): void {
    this.websocket?.send(data);
  }
}

export interface WebsocketListener {
  connected(): void;
  closed(): void;
  messageReceived(message: WebsocketMessage): void;
}

export interface WebsocketMessage {
  socket: string|null;
  channel: string;
  event: string;
  data: any;
}

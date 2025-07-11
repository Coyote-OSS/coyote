import WebsocketTransport, {WebsocketMessage} from "./WebsocketTransport";

export class CoyoteSocketClient {
  constructor(
    private websocketUrl: string,
    private subscriptionCommand: string,
    private listener: CoyoteSocketListener,
  ) {}

  start(): void {
    const that = this;
    const coyoteWebsocket = new WebsocketTransport(this.websocketUrl, {
      connected(): void {
        coyoteWebsocket.send(that.subscriptionCommand);
      },
      messageReceived(message: WebsocketMessage): void {
        if (message.event === 'Illuminate\\Notifications\\Events\\BroadcastNotificationCreated') {
          that.listener.notificationReceived();
        }
        if (message.event === 'PmCreated') {
          that.listener.privateMessageReceived(message.data['count']);
        }
      },
      closed(): void {},
    });
  }
}

export interface CoyoteSocketListener {
  notificationReceived(): void;
  privateMessageReceived(messagesCount: number): void;
}

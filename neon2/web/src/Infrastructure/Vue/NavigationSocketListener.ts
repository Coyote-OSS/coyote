import {CoyoteSocketListener} from "../Backend/CoyoteSocketClient";
import {NavigationStore, useNavigationStore} from "./NavigationView/View/navigationStore";

export class NavigationSocketListener implements CoyoteSocketListener {
  notificationReceived(): void {
    const store: NavigationStore = useNavigationStore();
    store.navigationUser!.notificationsCount++;
    store.navigationUser!.notifications = [];
  }

  privateMessageReceived(messagesCount: number): void {
    const store: NavigationStore = useNavigationStore();
    store.navigationUser!.messagesCount = messagesCount;
  }
}

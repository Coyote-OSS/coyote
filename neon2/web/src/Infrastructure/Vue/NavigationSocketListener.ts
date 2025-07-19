import {NavigationUser} from "../../Domain/Navigation/NavigationUser";
import {CoyoteSocketListener} from "../Backend/CoyoteSocketClient";
import {NavigationStore, useNavigationStore} from "./NavigationView/View/navigationStore";

export class NavigationSocketListener implements CoyoteSocketListener {
  notificationReceived(): void {
    const store: NavigationStore = useNavigationStore();
    const user: NavigationUser = store.navigationUser!;
    user.notificationsCount++;
    user.notifications = [];
  }

  privateMessageReceived(messagesCount: number): void {
    const store: NavigationStore = useNavigationStore();
    const user: NavigationUser = store.navigationUser!;
    user.messagesCount = messagesCount;
  }
}

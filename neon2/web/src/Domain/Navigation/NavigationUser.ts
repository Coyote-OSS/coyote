import {Notification} from "./Notification";

export interface NavigationUser {
  username: string;
  profileHref: string;
  messagesCount: number;
  notificationsCount: number;
  notifications: Notification[];
  avatarUrl: string|null;
  avatarInitials: string;
  canAccessAdministratorPanel: boolean;
  websocketSubscribeCommand: string;
}

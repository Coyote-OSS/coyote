export interface NavigationUser {
  username: string;
  profileHref: string;
  messagesCount: number;
  notificationsCount: number;
  avatarUrl: string|null;
  avatarInitials: string;
  canAccessAdministratorPanel: boolean;
}

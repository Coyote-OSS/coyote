export interface Notification {
  notificationTitle: string;
  notificationDate: string;
  notificationHighlighted: boolean;
  contentTitle: string;
  contentPreview: string;
  contentHref: string;
  actorAvatarUrl: string|null;
  actorInitials: string;
  actorProfileHref: string;
}

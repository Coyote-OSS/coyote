export type NavigationAction =
  'jobBoard'|'pricing'|'forum'|'blog'|
  'register'|'login'|'logout'|'help'|
  'account'|'profile'|'messages'|
  'toggleTheme'|'admin'|'employerReviews';

export interface NavigationService {
  action(action: NavigationAction): void;
  actionHref(action: NavigationAction): string|null;
  loadMoreNotifications(): void;
  viewNotifications(): void;
  markAllNotificationsAsViewed(): void;
  mainContentSuspended(suspended: boolean): void;
  search(searchPhrase: string): void;
}

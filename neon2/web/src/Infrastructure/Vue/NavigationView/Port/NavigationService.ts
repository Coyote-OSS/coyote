export type NavigationAction =
  'jobBoard'|'pricing'|'forum'|'blog'|
  'register'|'login'|'logout'|'help'|
  'account'|'profile'|'messages'|
  'toggleTheme'|'admin'|'employerReviews';

export interface NavigationService {
  action(action: NavigationAction): void;
  showJobBoard(): void;
  showPricing(): void;
  showForum(): void;
  showBlog(): void;
  attemptRegister(): void;
  attemptLogin(): void;
  attemptLogout(): void;
  attemptHelp(): void;
  attemptMessages(): void;
  attemptAdministratorPanel(): void;
  attemptAccount(): void;
  attemptProfile(): void;
  loadMoreNotifications(): void;
  markAllNotificationsAsViewed(): void;
  mainContentSuspended(suspended: boolean): void;
  showEmployerReviews(): void;
}

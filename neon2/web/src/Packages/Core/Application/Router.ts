export interface Router<R extends string> {
  addRoute(route: R, redirect?: RedirectGuard<R>): void;
  navigate(route: R, params: RouteParams): void;
  resolveUrl(route: R, params: RouteParams): string;
  addDefaultRoute(route: R): void;
}

export type RedirectGuard<R extends string> = (params: RouteParams) => R|null;

export interface RouteParams {
  id?: number;
  slug?: string;
}

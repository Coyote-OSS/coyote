import {App, Component} from "vue";
import {
  createRouter,
  createWebHistory,
  RouteLocationNormalized,
  RouteParamsRawGeneric,
  Router as ExternalRouter,
  useRoute,
} from "vue-router";
import {RedirectGuard, RouteParams, Router} from "../Router";

export type RouteUrlMap<R extends string> = Record<R, string>;
export type RouteComponentMap<R extends string> = Record<R, Component>;

export class VueRouter<R extends string> implements Router<R> {
  private readonly router: ExternalRouter = createRouter({
    history: createWebHistory(),
    routes: [],
    scrollBehavior() {
      return {top: 0};
    },
  });

  constructor(
    private urls: RouteUrlMap<R>,
    private components: RouteComponentMap<R>,
  ) {}

  useIn(app: App): void {
    app.use(this.router);
  }

  addDefaultRoute(route: R): void {
    this.router.addRoute({path: '/', redirect: {name: route}});
  }

  addRoute(
    route: R,
    redirect?: RedirectGuard<R>,
  ): void {
    const beforeEnter = redirect
      ? [((route: RouteLocationNormalized) => {
        const redirectTo = redirect(toParams(route.params));
        if (redirectTo) {
          return {name: redirectTo};
        }
      })]
      : [];
    this.router.addRoute({
      component: this.components[route],
      name: route,
      path: this.urls[route],
      beforeEnter,
    });
  }

  navigate(route: R, params: RouteParams): void {
    this.router.push({
      name: route,
      params: fromParams(params),
    });
  }

  resolveUrl(route: R, params: RouteParams): string {
    return this.router.resolve({name: route, params: fromParams(params)}).href;
  }
}

export function useRouteId(): number {
  const route = useRoute();
  return Number(route.params.id)!;
}

function toParams(routeParams: RouteParamsRawGeneric): RouteParams {
  if (routeParams.id) {
    return {id: Number(routeParams.id)};
  }
  return {};
}

function fromParams(params: RouteParams): RouteParamsRawGeneric {
  return {...params};
}

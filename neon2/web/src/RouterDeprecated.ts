import {App, Component} from "vue";
import {
  createRouter,
  createWebHistory,
  RouteLocationNormalized,
  RouteParamsGeneric,
  RouteParamsRawGeneric,
  Router as VueRouter,
  useRoute,
} from "vue-router";

export class RouterDeprecated<R extends string> {
  private readonly router: VueRouter = createRouter({
    history: createWebHistory(),
    routes: [],
    scrollBehavior() {
      return {top: 0};
    },
  });

  useIn(app: App): void {
    app.use(this.router);
  }

  addDefaultRoute(route: R): void {
    this.router.addRoute({path: '/', redirect: {name: route}});
  }

  addRoute(
    component: Component,
    route: R,
    routerPath: string,
    before?: (params: RouteParamsGeneric) => R|null,
  ): void {
    this.router.addRoute({
      component,
      name: route,
      path: routerPath,
      beforeEnter: before ? [((route: RouteLocationNormalized) => {
        const redirectTo = before(route.params);
        if (redirectTo) {
          return {name: redirectTo};
        }
      })] : [],
    });
  }

  navigate(route: R, routeArguments: RouteParamsRawGeneric): void {
    this.router.push({
      name: route,
      params: routeArguments,
    });
  }
}

export function useRouteId(): number {
  const route = useRoute();
  return Number(route.params.id)!;
}

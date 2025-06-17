import {App, Component} from "vue";
import {
  createRouter,
  createWebHistory,
  RouteLocationNormalized,
  RouteParamsGeneric,
  Router as VueRouter,
  useRoute,
} from "vue-router";
import {Screen} from "./ui";

export class Router {
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

  addDefaultScreen(screen: Screen): void {
    this.router.addRoute({path: '/', redirect: {name: screen}});
  }

  addScreen(
    component: Component,
    screen: Screen,
    vueRouterRoute: string,
    before?: (params: RouteParamsGeneric) => Screen|null,
  ): void {
    this.router.addRoute({
      component,
      name: screen,
      path: vueRouterRoute,
      beforeEnter: before ? [((route: RouteLocationNormalized) => {
        const redirectTo = before(route.params);
        if (redirectTo) {
          return {name: redirectTo};
        }
      })] : [],
    });
  }

  navigate(screen: Screen, routeArguments: {id: number|null, slug?: string}): void {
    this.router.push({
      name: screen,
      params: routeArguments,
    });
  }
}

export function useRouteId(): number {
  const route = useRoute();
  return Number(route.params.id)!;
}

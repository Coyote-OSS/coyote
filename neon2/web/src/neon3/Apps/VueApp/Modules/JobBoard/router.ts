import {Component} from "vue";
import {createRouter, createWebHistory, Router, useRoute} from "vue-router";
import {ScreenName} from "../../../../Packages/Feature/JobBoard/Application/Model";
import JobOfferCreate from "./View/JobOfferCreate.vue";
import JobOfferEdit from "./View/JobOfferEdit.vue";
import JobOfferHome from "./View/JobOfferHome.vue";
import JobOfferPaymentScreen from "./View/JobOfferPaymentScreen.vue";
import JobOfferPricing from "./View/JobOfferPricing.vue";
import JobOfferShowScreen from "./View/JobOfferShowScreen.vue";

export function router(defaultRoute: ScreenName): Router {
  const vueRouter = createRouter({
    history: createWebHistory(),
    routes: [],
    scrollBehavior() {
      return {top: 0};
    },
  });

  function addRoute(component: Component, screenName: ScreenName, path: string) {
    vueRouter.addRoute({path, component, name: screenName});
  }

  addRoute(JobOfferHome, 'home', '/Job');
  addRoute(JobOfferShowScreen, 'show', '/Job/:slug/:id');
  addRoute(JobOfferPricing, 'pricing', '/Job/pricing');
  addRoute(JobOfferCreate, 'form', '/Job/new');
  addRoute(JobOfferEdit, 'edit', '/Job/:id/edit');
  function addDefaultRoute() {
    vueRouter.addRoute({path: '/', redirect: {name: defaultRoute}});
  }

  addRoute(JobOfferPaymentScreen, 'payment', '/Job/:id/payment');

  addDefaultRoute();
  return vueRouter;
}

// navigate(route: R, routeArguments: RouteParamsRawGeneric): void {
//   this.router.push({
//     name: route,
//     params: routeArguments,
//   });
// }

export function useRouteId(): number {
  const route = useRoute();
  return Number(route.params.id)!;
}

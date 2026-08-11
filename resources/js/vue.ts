import {App, createApp, nextTick as vueNextTick, toRaw} from "vue";
import axiosErrorHandler from './libs/axios-error-handler.js';
import store from "./store/index";
import {install, notify} from './toast';

const icons = window['icons'];

export function nextTick(block: () => void): void {
  vueNextTick(block);
}

export function removeVueProxy(dataInstance) {
  return toRaw(dataInstance);
}

export function createVueApp(name: string, selector: string, component: object): App<Element> {
  const app = createApp({...component, name});
  app.use(store);
  app.provide('icons', icons);
  app.mount(selector);
  return app;
}

type CustomElementPredicate = (tag: string) => boolean;

export function createVueAppNotifications(
  name: string,
  selector: string,
  component: object,
  isCustomElement?: CustomElementPredicate,
): void {
  const app = createApp({...component, name});
  if (isCustomElement) {
    app.config.compilerOptions.isCustomElement = isCustomElement;
  }
  install(app);
  app.use(store);
  app.provide('icons', icons);
  app.mount(selector);
}

export function setAxiosErrorVueNotification(): void {
  axiosErrorHandler((message: string) => notify({type: 'error', text: message}));
}

export function createVueAppGhost(component: object, properties: object): [App<Element>, Element] {
  const app = createApp(component, {...properties});
  app.use(store);
  app.provide('icons', icons);
  const domElement = document.createElement('div');
  app.mount(domElement);
  return [app, domElement];
}

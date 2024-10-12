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

export function createVueAppNotifications(name: string, selector: string, component: object): void {
  const app = createApp({...component, name});
  install(app);
  app.use(store);
  app.provide('icons', icons);
  app.mount(selector);
}

export function setAxiosErrorVueNotification(): void {
  axiosErrorHandler((message: string) => notify({type: 'error', text: message}));
}

export function createVueAppPhantom(component: object, properties: Record<string, unknown>): Element {
  const app = createApp(component, properties);
  app.use(store);
  app.provide('icons', icons);
  const el = document.createElement('div');
  app.mount(el);
  return el;
}

export function createVueAppGhost(component: object, properties: object): [App<Element>, Element] {
  const app = createApp(component, {...properties});
  app.use(store);
  app.provide('icons', icons);
  const domElement = document.createElement('div');
  app.mount(domElement);
  return [app, domElement];
}

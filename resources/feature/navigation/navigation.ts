import {createApp} from "../../../neon2/web/node_modules/vue";
import {CoyoteSearchPrompt} from "../../../neon2/web/src/Application/Navigation/CoyoteSearchPrompt";
import {NavigationUser} from "../../../neon2/web/src/Domain/Navigation/NavigationUser";
import {CoyoteApi} from "../../../neon2/web/src/Infrastructure/Backend/CoyoteApi";
import {CoyoteSocketClient} from "../../../neon2/web/src/Infrastructure/Backend/CoyoteSocketClient";
import {NavigationSocketListener} from "../../../neon2/web/src/Infrastructure/Vue/NavigationSocketListener";
import {NavigationForumMenu} from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/NavigationForumMenu";
import {NavigationView} from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/NavigationView";
import {ThemeController} from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/ThemeController";
import NavigationTopbar from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/View/Component/NavigationTopbar.vue";
import {useNavigationStore} from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/View/navigationStore";
import {navigationServiceInjectKey} from "../../../neon2/web/src/Infrastructure/Vue/NavigationView/View/vue";
import {pinia} from "../../../neon2/web/src/Infrastructure/Vue/pinia";
import {CoyoteNavigationService} from "./CoyoteNavigationService";

declare global {
  interface Window {
    navigationMenu: NavigationForumMenu;
    navigationUser: NavigationUser|null;
  }
}

const app = createApp(NavigationTopbar);
app.use(pinia);

const store = useNavigationStore();
const naviView = new NavigationView(store);
const csrfToken = htmlMetaAttribute('csrf-token');
const coyoteApi = new CoyoteApi(csrfToken);
store.navigationForumMenu = window.navigationMenu;
store.navigationUser = window.navigationUser;
store.isAuthenticated = window.navigationUser !== null;
store.darkTheme = window.document.body.classList.contains('theme-dark');
app.provide(navigationServiceInjectKey, new CoyoteNavigationService(
  csrfToken,
  naviView,
  new ThemeController(naviView, coyoteApi),
  coyoteApi,
  new CoyoteSearchPrompt(),
));

if (window.navigationUser) {
  const client = new CoyoteSocketClient(
    htmlMetaAttribute('websocket-url'),
    window.navigationUser.websocketSubscribeCommand,
    new NavigationSocketListener());
  client.start();
}

mountVueNavigation();
window.addEventListener('load', () => mountVueNavigation());

function mountVueNavigation(): void {
  findHeaderShadowRoot(shadowRoot => {
    const appRoot = shadowRoot.querySelector('#vue-navigation');
    if (appRoot && appRoot.childNodes.length === 0) {
      app.mount(appRoot);
    }
  });
}

function findHeaderShadowRoot(runnable: (root: ShadowRoot) => void): void {
  const header = document.querySelector('header');
  if (!header) return;
  if (header.shadowRoot) {
    runnable(header.shadowRoot);
  } else if (shadowDomSupported()) {
    attachShadow(header, header.getElementsByTagName('template')[0]);
    runnable(header.shadowRoot!);
  }
}

function htmlMetaAttribute(metaAttribute: string): string {
  return document
    .querySelector('meta[name="' + metaAttribute + '"]')!
    .getAttribute('content')!;
}

function attachShadow(host: HTMLElement, template: HTMLTemplateElement): void {
  const shadowRoot = host.attachShadow({mode: 'open'});
  shadowRoot.appendChild(template.content.cloneNode(true));
}

function shadowDomSupported(): boolean {
  return document.head['createShadowRoot'] || document.head.attachShadow;
}

import {type Component, createApp} from 'vue';
import '../../../tailwind/tailwind.css';
import {nonNull} from "../../../libs/typing/typing";
import ForumJobOffersPage from './ForumJobOffersPage.vue';

function main(): void {
  const app = createApp(pageComponent(queryParam('view')));
  app.mount('#harness');
}

function pageComponent(viewName: string): Component {
  const views: Record<string, Component> = {
    'forum-job-offers': ForumJobOffersPage,
  };
  if (viewName in views) {
    return views[viewName];
  }
  throw new Error(`Unknown harness view: ${viewName}`);
}

function queryParam(name: string): string {
  const queryParams = new URLSearchParams(location.search);
  return nonNull(queryParams.get(name));
}

main();

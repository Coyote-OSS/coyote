import {defineCustomElement} from "vue";
import fontAwesomeStyles from '../../../web/libs/Icon/fontAwesome.css?inline';
import fontAwesomeIconsStyles from '../../../web/libs/Icon/fontAwesomeIcons.css?inline';
import {ForumJobOfferTile} from "../../../web/projections/ForumJobOffers/ViewModel/ForumJobOfferTile";
import * as Models from "../types/models";
import {createVueApp, createVueAppNotifications, setAxiosErrorVueNotification} from "../vue";
import VueForum from './forum/homepage';
import VueLog from './forum/log';
import VuePosts from './forum/posts';
import VueSidebar from './forum/sidebar';
import VueTags from './forum/tags';
import tailwindStyles from "./vue-shadow-root.css?inline";
import VueShadowRoot from "./vue-shadow-root.vue";

declare global {
  interface Window {
    pagination: Models.Paginator;
    topic: Models.Topic;
    post: Models.Post;
    forum: Models.Forum;
    poll: Models.Poll;
    tags: Models.Tag[];
    emojis: Models.Emojis;
    showStickyCheckbox: boolean;
    showDiscussModeSelect: boolean;
    reasons: string[];
    forumJobOfferTiles: ForumJobOfferTile[];
    allForums: Models.Forum[];
    showCategoryName: boolean;
    groupStickyTopics: boolean;
    topics: Models.Paginator;
    popularTags: string[];
    logs: Models.PostLog[];
    topicLink: string;
  }
}

setAxiosErrorVueNotification();

exists('#js-forum') && createVueAppNotifications('Forum', '#js-forum', VueForum);
exists('#js-post') && createVueAppNotifications('Posts', '#js-post', VuePosts, tag => tag === 'vue-shadow-root');
exists('#js-log') && createVueApp('Log', '#js-log', VueLog);
createVueApp('Sidebar', '#js-sidebar', VueSidebar);
createVueApp('Tags', '#js-tags', VueTags);

function exists(selector: string): boolean {
  return !!document.querySelector(selector);
}

document.getElementById('js-forum-list')
  ?.addEventListener('change', event => window.location.href = `/Forum/${(event.target as HTMLSelectElement).value}`);

document.getElementById('js-reload')
  ?.addEventListener('click', () => window.location.reload());

document.getElementById('js-per-page')
  ?.addEventListener('change', event => {
    const perPage = (event.target as HTMLSelectElement).value;
    const url = (event.target as HTMLSelectElement).dataset.url;
    window.location.href = `${url}?perPage=${perPage}`;
  });

document.getElementById('btn-toggle-sidebar')
  ?.addEventListener('click', function () {
    document.getElementById('sidebar')!.classList.toggle('d-block');
    return false;
  });

if (!document.head.querySelector('style[data-shadow-root-fonts]')) {
  const fontFaceStyle = document.createElement('style');
  fontFaceStyle.setAttribute('data-shadow-root-fonts', '');
  fontFaceStyle.textContent = fontAwesomeStyles.toString();
  document.head.appendChild(fontFaceStyle);
}

customElements.define('vue-shadow-root', defineCustomElement(VueShadowRoot, {
  styles: [
    tailwindStyles.toString(),
    fontAwesomeStyles.toString(),
    fontAwesomeIconsStyles.toString(),
  ],
}));

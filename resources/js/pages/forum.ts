import * as Models from "../types/models";
import {createVueApp, createVueAppNotifications, setAxiosErrorVueNotification} from "../vue";

import VueForum from './forum/homepage';
import VueLog from './forum/log';
import VuePosts from './forum/posts';
import VueSidebar from './forum/sidebar';
import VueTags from './forum/tags';

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
    jobOffers: string[];
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
exists('#js-post') && createVueAppNotifications('Posts', '#js-post', VuePosts);
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

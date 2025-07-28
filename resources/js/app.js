import Prism from 'prismjs';
import 'bootstrap/js/dist/tooltip.js';
import 'bootstrap/js/dist/collapse.js';
import 'bootstrap/js/dist/dropdown.js';
import 'bootstrap/js/dist/modal.js';
import 'bootstrap/js/dist/tab.js';
import './components/navbar-toggle.ts';
import './components/bannerCount.ts';
import './components/scrolltop.js';
import './components/vcard.js';
import './legacy/state.js';
import './libs/axios-throttle.ts';
import './libs/csrf.js'; // setup CSRF token
import Router from './libs/router.js';
import './libs/timeago.js';
import './plugins/flags.ts';
import './plugins/sociale.js';
import './sentry.ts';
import './store/index.ts';
import './gdpr.ts';
import '../feature/stickyAside/sticky-aside.js';
import '../feature/post/copyButton';
import '../feature/jobBoard/jobBoard';
import '../feature/navigation/navigation';

Prism.highlightAll();

new Router()
  .on(['/User', '/User/Skills', '/User/Relations', '/User/Tokens'], () => require('./pages/user'))
  .on(['/Register', '/Login'], () => require('./pages/auth'))
  .on(['/User/Pm/Submit', '/User/Pm/Show/*', '/User/Pm'], () => require('./pages/pm'))
  .on(['/Profile/*/Reputation'], () => require('./pages/profile'))
  .on(['/Profile/*', '/Profile/*/Microblog'], () => {
    require('./pages/profile');
    require('./pages/microblog');
  })
  .on(['/Mikroblogi', '/Mikroblogi/*'], () => require('./pages/microblog'))
  .on(['/'], () => require('./pages/homepage'))
  .on(['/Search'], () => require('./pages/search'))
  .resolve();

if ('serviceWorker' in navigator) {
  navigator.serviceWorker
    .register('/sw.js')
    .catch(error => console.log(error));
}

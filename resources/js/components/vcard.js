import axios from 'axios';
import {createVueApp} from '../vue';
import VueFollowButton from './forms/follow-button.vue';

let tooltipTimer;

function removeVCard() {
  document.getElementById('vcard')?.remove();
}

function vCardDisabled(element) {
  return element.closest('.home-members--docked, .pm-chat') !== null;
}

function showVCard(event) {
  if (vCardDisabled(event.target)) {
    return;
  }
  clearTimeout(tooltipTimer);
  const userId = event.target.dataset.userId;
  const anchorPoint = elementAnchorPoint(event);

  tooltipTimer = setTimeout(handler, 250);

  function handler() {
    axios.get(`/User/Vcard/${userId}`).then(result => {
      removeVCard();
      const container = document.createElement('div');
      container.innerHTML = result.data;
      document.body.appendChild(container);

      createVueApp('VCard', '#vcard', {components: {'vue-follow-button': VueFollowButton}});

      const vcard = document.getElementById('vcard');
      if (!placeBesidePanel(vcard, event.target)) {
        vcard.style.top = `${anchorPoint.top + 20}px`;
        vcard.style.left = `${Math.min(anchorPoint.left, window.innerWidth - 520)}px`;
      }

      vcard.addEventListener('mouseenter', () => clearTimeout(tooltipTimer));
      vcard.addEventListener('mouseleave', removeVCard);
    });
  }
}

/**
 * In the homepage community list, the vcard is shown to the left of the panel, level with the row,
 * so it doesn't cover the row's buttons or the users below.
 */
function placeBesidePanel(vcard, element) {
  const panel = element.closest('.home-members');
  if (!panel) {
    return false;
  }
  const gap = 8;
  const panelRect = panel.getBoundingClientRect();
  if (panelRect.left - gap < vcard.offsetWidth) {
    return false; // no room beside the panel
  }
  const rowRect = element.getBoundingClientRect();
  const top = Math.max(gap, Math.min(rowRect.top, window.innerHeight - vcard.offsetHeight - gap));
  vcard.style.top = `${top + window.scrollY}px`;
  vcard.style.left = `${panelRect.left - vcard.offsetWidth - gap + window.scrollX}px`;
  return true;
}

function elementAnchorPoint(event) {
  const element = event.target;
  if (element.getBoundingClientRect) {
    const {left, top} = element.getBoundingClientRect();
    return {left: left + window.scrollX, top: top + window.scrollY};
  }
  return {left: event.pageX, top: event.pageY};
}

function hideVCard() {
  clearTimeout(tooltipTimer);
  tooltipTimer = setTimeout(removeVCard, 1500);
}

function bindEvents() {
  const links = document.querySelectorAll('a[data-user-id]');
  links.forEach(link => {
    link.addEventListener('mouseenter', showVCard);
    link.addEventListener('mouseleave', hideVCard);
  });
}

new MutationObserver(bindEvents).observe(document.body, {attributes: true, childList: true, subtree: true});

import axios from 'axios';
import {createVueApp} from '../vue';
import VueFollowButton from './forms/follow-button.vue';

let tooltipTimer;

function removeVCard() {
  document.getElementById('vcard')?.remove();
}

function vCardDisabled(element) {
  return element.closest('.home-members--docked') !== null;
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
      vcard.style.top = `${anchorPoint.top + 20}px`;
      vcard.style.left = `${Math.min(anchorPoint.left, window.innerWidth - 520)}px`;

      vcard.addEventListener('mouseenter', () => clearTimeout(tooltipTimer));
      vcard.addEventListener('mouseleave', removeVCard);
    });
  }
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

import {sendBeacon} from '../exposure/sendBeacon';
import {asHtmlImageElement} from '../exposure/typing';
import {isBannerHidden} from './isBannerHidden';
import {string} from "./typing";

function checkBanners(): void {
  Array
    .from(document.querySelectorAll('.campaign-banner img[data-adblock-url]'))
    .forEach((element: Element): void => {
      const image = asHtmlImageElement(element);
      if (isBannerHidden(image)) {
        sendBeacon(string(image.dataset['adblockUrl']));
      }
    });
}

window.addEventListener('load', () => window.setTimeout(checkBanners, 1000));

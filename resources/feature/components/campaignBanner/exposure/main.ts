import {BannerExposureObserver} from './BannerExposureObserver';
import {sendBeacon} from './sendBeacon';
import {asHtmlImageElement} from './typing';

if (typeof IntersectionObserver !== 'undefined') {
  Array
    .from(document.querySelectorAll('.campaign-banner img[data-expose-url]'))
    .forEach((element: Element): void => {
      const image = asHtmlImageElement(element);
      const exposeUrl = image.dataset['exposeUrl'];
      if (exposeUrl) {
        new BannerExposureObserver(
          image,
          0.5, // fraction of the banner that must be visible
          1000, // how long it must stay visible to count as exposure
          () => sendBeacon(exposeUrl),
        ).observe();
      }
    });
}

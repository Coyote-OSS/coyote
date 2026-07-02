import {BannerExposureObserver} from './BannerExposureObserver';
import {sendBeacon} from './sendBeacon';
import {asHtmlImageElement} from './typing';

if (typeof IntersectionObserver !== 'undefined') {
  Array
    .from(document.querySelectorAll('.campaign-banner img[data-variant-id]'))
    .forEach((element: Element): void => {
      const image = asHtmlImageElement(element);
      const variantId = image.dataset['variantId'];
      if (variantId) {
        new BannerExposureObserver(
          image,
          0.5, // fraction of the banner that must be visible
          1000, // how long it must stay visible to count as exposure
          () => sendBeacon(`/campaigns/${variantId}/expose`),
        ).observe();
      }
    });
}

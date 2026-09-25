import {observeExposure} from '../../../../../web/libs/Exposure/observeExposure';
import {sendBeacon} from './sendBeacon';
import {asHtmlImageElement} from './typing';

Array
  .from(document.querySelectorAll('.campaign-banner img[data-expose-url]'))
  .forEach((element: Element): void => {
    const image = asHtmlImageElement(element);
    const exposeUrl = image.dataset['exposeUrl'];
    if (exposeUrl) {
      observeExposure(image, () => sendBeacon(exposeUrl));
    }
  });

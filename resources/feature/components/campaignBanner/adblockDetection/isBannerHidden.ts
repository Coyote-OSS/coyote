export function isBannerHidden(image: HTMLImageElement): boolean {
  // element (or an ancestor) has display:none, or isn't in the document at all
  if (image.offsetParent === null) {
    return true;
  }
  const style = window.getComputedStyle(image);
  if (style.display === 'none' || style.visibility === 'hidden') {
    return true;
  }
  // the image request itself never completed - typical of network/domain-based blocking
  return image.complete && image.naturalWidth === 0;
}

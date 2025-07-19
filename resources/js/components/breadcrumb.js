function getElementByClass(name) {
  return document.getElementsByClassName(name)[0];
}

function navigationOffsetLeft() {
  return document
    .querySelector('header')
    .shadowRoot
    .querySelector('.breadcrumb-offset-reference')
    .offsetLeft;
}

function handleScroll() {
  let breadcrumb = document.getElementById('breadcrumb-fixed');

  if (document.documentElement.scrollTop < 150) {
    breadcrumb?.remove();
    return;
  }

  if (!breadcrumb) {
    breadcrumb = getElementByClass('breadcrumb')?.cloneNode(true);

    if (!breadcrumb) {
      return;
    }

    breadcrumb.id = 'breadcrumb-fixed';
    breadcrumb.style.left = `${navigationOffsetLeft()}px`;

    document.body.append(breadcrumb);
  }
}

function handleResize() {
  const breadcrumb = document.getElementById('breadcrumb-fixed');
  if (breadcrumb) {
    breadcrumb.style.left = `${navigationOffsetLeft()}px`;
  }
}

function adjustHashOffset() {
  window.scrollTo(window.scrollX, window.scrollY - 60);
}

if (isDesktop()) {
  window.addEventListener('hashchange', adjustHashOffset);
  window.addEventListener('load', () => {
    if (window.location.hash) {
      adjustHashOffset();
    }
  });

  window.addEventListener('scroll', handleScroll, {passive: true});
  window.addEventListener('resize', handleResize, {passive: true});
}

function isDesktop() {
  return !isMobile();
}

function isMobile() {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

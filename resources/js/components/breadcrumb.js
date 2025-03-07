function getElementByClass(name) {
  return document.getElementsByClassName(name)[0];
}

function handleScroll() {
  let breadcrumb = document.getElementById('breadcrumb-fixed');

  if (document.documentElement.scrollTop < 150) {
    breadcrumb?.remove();

    return;
  }

  const header = document.querySelector('header');

  if (!breadcrumb) {
    breadcrumb = getElementByClass('breadcrumb')?.cloneNode(true);

    if (!breadcrumb) {
      return;
    }

    breadcrumb.id = 'breadcrumb-fixed';
    breadcrumb.style.left = `${header.offsetLeft}px`;

    document.body.append(breadcrumb);
  }
}

function handleResize() {
  const breadcrumb = document.getElementById('breadcrumb-fixed');
  if (breadcrumb) {
    const logo = getElementByClass('navbar-brand');
    breadcrumb.style.left = `${logo.offsetLeft}px`;
  }
}

function adjustHashOffset() {
  window.scrollTo(window.scrollX, window.scrollY - 60);
}

const isMobile = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));

if (!isMobile && getElementByClass('fixed-top')) {
  window.addEventListener('hashchange', adjustHashOffset);

  window.addEventListener('load', () => {
    if (window.location.hash) {
      adjustHashOffset();
    }
  });

  window.addEventListener('scroll', handleScroll, {passive: true});
  window.addEventListener('resize', handleResize, {passive: true});
}
